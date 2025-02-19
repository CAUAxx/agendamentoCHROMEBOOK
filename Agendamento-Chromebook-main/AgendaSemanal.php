<?php
require_once 'auth.php'; // Autenticação de sessão (verifique se a sessão está ativa)
// Função para obter o início da semana (segunda-feira) para uma determinada data
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

function obterInicioDaSemana($data)
{
    $diaSemana = date('w', strtotime($data));
    $diasParaSegunda = ($diaSemana == 0) ? 6 : $diaSemana - 1;
    return date('d-m-Y', strtotime($data . " - $diasParaSegunda days"));
}

// Função para gerar os horários do agendamento
function gerarHorarios()
{
    $horarios = [];

    // horario da manha
    $hora = 7; //hora que comeca
    $minuto = 10; //minuto que comeca
    while ($hora < 13) {  //horario para acabar
        $hora_formatada = str_pad($hora, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minuto, 2, '0', STR_PAD_LEFT); //formato hora
        $horarios[$hora_formatada] = [ //dias da semana
            'Segunda' => [],
            'Terça' => [],
            'Quarta' => [],
            'Quinta' => [],
            'Sexta' => []
        ];

        // verificando se chegou no intervalo de 9:40
        if ($hora == 9 && $minuto == 40) {
            // adiciona 09:50 e ajusta o incremento
            $minuto = 50;
            $hora_formatada = str_pad($hora, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minuto, 2, '0', STR_PAD_LEFT);
            $horarios[$hora_formatada] = [
                'Segunda' => [],
                'Terça' => [],
                'Quarta' => [],
                'Quinta' => [],
                'Sexta' => []
            ];
            // ajusta para que o proximo incremento siga o padrao normal
            $minuto = 40;
            $hora++;
        } else {
            // aumenta 50 minutos
            $minuto += 50;
            if ($minuto >= 60) {
                $minuto -= 60;
                $hora++;
            }
        }
    }

    // horario da noite
    $hora = 18; //hora que comeca
    $minuto = 00; //minuto que comeca
    while ($hora < 22 || ($hora == 22 && $minuto == 10)) {  //horario para acabar
        $hora_formatada = str_pad($hora, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minuto, 2, '0', STR_PAD_LEFT); //formato hora
        $horarios[$hora_formatada] = [ //dias da semana
            'Segunda' => [],
            'Terça' => [],
            'Quarta' => [],
            'Quinta' => [],
            'Sexta' => []
        ];

        // verificando se chegou no intervalo de 9:40
        if ($hora == 20 && $minuto == 40) {
            // adiciona 20:50 e ajusta o incremento
            $minuto = 50;
            $hora_formatada = str_pad($hora, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minuto, 2, '0', STR_PAD_LEFT);
            $horarios[$hora_formatada] = [
                'Segunda' => [],
                'Terça' => [],
                'Quarta' => [],
                'Quinta' => [],
                'Sexta' => []
            ];
            // ajusta para que o proximo incremento siga o padrao normal
            $minuto = 30;
            $hora++;
        } else {
            // aumenta 50 minutos
            $minuto += 40;
            if ($minuto >= 60) {
                $minuto -= 60;
                $hora++;
            }
        }
    }
    return $horarios;
}


// Verifica se o parâmetro id_professor foi passado na URL
if (isset($_GET['id_professor'])) {
    $id_professor = $_GET['id_professor'];

    // Conexão com o banco de dados
    $conn = new mysqli("localhost", "root", "", "chromebook");
    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    // Obter a data da semana atual ou da semana passada através de parâmetros GET
    $hoje = isset($_GET['data']) ? $_GET['data'] : date('d-m-Y');
    $inicioDaSemana = obterInicioDaSemana($hoje);
    $fimDaSemana = date('d-m-Y', strtotime($inicioDaSemana . ' + 6 days'));
    $horarios = gerarHorarios();

    // Consulta SQL para obter os agendamentos da semana
    $sql = "SELECT agendamento.*, professor.nome AS professor_nome, agendamento.idCor
        FROM agendamento
        JOIN professor ON agendamento.id_professor = professor.id
        WHERE STR_TO_DATE(agendamento.data, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') 
        AND STR_TO_DATE(?, '%d-%m-%Y')";

    // Prepara a consulta SQL
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Erro na preparação da consulta SQL: ' . $conn->error);
    }

    // Vincula os parâmetros à consulta preparada
    $stmt->bind_param("ss", $inicioDaSemana, $fimDaSemana);

    // Executa a consulta
    $stmt->execute();

    // Obtém o resultado da consulta
    $result = $stmt->get_result();

    // Processa os resultados da consulta
    if ($result->num_rows > 0) {
        while ($agendamento = $result->fetch_assoc()) {
            $inicio = strtotime($agendamento['data'] . ' ' . $agendamento['horario']);
            $hora_formatada = date('H:i', $inicio);
            $diaSemana = date('l', $inicio); // Obtém o dia da semana completo (ex: Monday, Tuesday, etc.)

            // Converte o dia da semana para o nome em português
            switch ($diaSemana) {
                case 'Monday':
                    $diaSemana = 'Segunda';
                    break;
                case 'Tuesday':
                    $diaSemana = 'Terça';
                    break;
                case 'Wednesday':
                    $diaSemana = 'Quarta';
                    break;
                case 'Thursday':
                    $diaSemana = 'Quinta';
                    break;
                case 'Friday':
                    $diaSemana = 'Sexta';
                    break;
            }

            // Adiciona o agendamento ao array de horários exibe pro usuario
            if (isset($horarios[$hora_formatada][$diaSemana])) {
                $horarios[$hora_formatada][$diaSemana][] = [
                    'professor_nome' => $agendamento['professor_nome'],  // Exibe o nome do professor
                    'data_hora' => $agendamento['data'] . ' ' . $agendamento['horario'],
                    'idCor' => $agendamento['idCor'],
                    "idProf" => $agendamento['id_professor'],
                    'id' => $agendamento['id']
                ];
            } else {
                $horarios[$hora_formatada][$diaSemana] = [];
            }
        }
    }

    // Fecha a conexão e a consulta
    $stmt->close();
    $conn->close();
}

// Código para logout
if (isset($_POST['logout'])) {
    session_start();
    session_unset(); // Destrói todas as variáveis de sessão
    session_destroy(); // Destrói a sessão
    header("Location: Login.html"); // Redireciona para a página de login
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento Chromebook</title>
    <style>
        body {
            font-family: "Roboto", sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .logout-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .logout-btn:hover {
            background-color: darkred;
        }

        .navegacao {
            text-align: center;
            margin-bottom: 20px;
        }

        .navegacao a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #1B98E0;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }

        .navegacao a:hover {
            background-color: #006494;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow-x: auto; /* Adiciona rolagem horizontal em telas pequenas */
        }

        th,
        td {
            border: 1px solid #000;
            text-align: center;
            padding: 8px;
            width: 150px;
        }

        th {
            background-color: #f2f2f2;
        }

        td {
            text-align: center;
            vertical-align: top;
        }

        .manha {
            background-color: #e0f7fa;
        }

        .noite {
            background-color: #ffe0b2;
        }

        .agendamento {
            border-radius: 4px;
            margin-bottom: 4px;
            margin-left: auto;
            margin-right: auto;
            font-size: 14px;
            padding: 2px;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            max-width: 275px;
        }

        .agendamento strong {
            display: block;
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }

        .agendamento button {
            background-color: #ff4c4c;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 3px;
        }

        .agendamento button:hover {
            background-color: #ff1f1f;
        }

        .agendamento button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 76, 76, 0.5);
        }

        /* Estilos responsivos */
        @media (max-width: 768px) {
            th,
            td {
                padding: 5px;
                font-size: 12px; /* Reduz o tamanho da fonte em telas menores */
            }

            .agendamento {
                max-width: 100%; /* Permite que os agendamentos ocupem toda a largura disponível */
            }

            .logout-btn {
                font-size: 14px; /* Ajusta o tamanho do botão de logout */
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 18px; /* Reduz o tamanho do título em telas muito pequenas */
            }

            .navegacao a {
                padding: 8px 10px; /* Ajusta o padding dos links de navegação */
                font-size: 12px; /* Reduz o tamanho da fonte dos links */
            }
        }
    </style>
</head>

<body>
    <?php

    switch (date("l", strtotime($hoje))) {
        case "Monday":
            $nomeDia = "segunda-feira";
            break;
        case "Tuesday":
            $nomeDia = "terça-feira";
            break;
        case "Wednesday":
            $nomeDia = "quarta-feira";
            break;
        case "Thursday":
            $nomeDia = "quinta-feira";
            break;
        case "Friday":
            $nomeDia = "sexta-feira";
            break;
        case "Saturday";
            $nomeDia = "sábado";
            break;
        case "Sunday":
            $nomeDia = "domingo";
            break;
    }

    switch (date("F", strtotime($hoje))) {
        case "January":
            $nomeMes = "Janeiro";
            break;
        case "February":
            $nomeMes = "Fevereiro";
            break;
        case "March":
            $nomeMes = "Março";
            break;
        case "April":
            $nomeMes = "Abril";
            break;
        case "May":
            $nomeMes = "Maio";
            break;
        case "June";
            $nomeMes = "Junho";
            break;
        case "July":
            $nomeMes = "Julho";
            break;
        case "August":
            $nomeMes = "Agosto";
            break;
        case "September":
            $nomeMes = "Setembro";
            break;
        case "October":
            $nomeMes = "Outubro";
            break;
        case "November":
            $nomeMes = "Novembro";
            break;
        case "December":
            $nomeMes = "Dezembro";
            break;
    }

    ?>

    <form method="post" action="AgendaSemanal.php">
        <button type="submit" name="logout" class="logout-btn">Sair</button>
    </form>

    <h1>Agendamento Semanal dos Chromebooks</h1>
    <h1>Data Atual:
        <?php echo $nomeDia . ", " . date("d", strtotime($hoje)) . " de " . $nomeMes . " de " . date("Y", strtotime($hoje)); ?>
    </h1>

    <div class="navegacao">
        <a href="Agendamento.php">Agendar</a>
        <a
            href="?id_professor=<?php echo $id_professor; ?>&data=<?php echo date('d-m-Y', strtotime($inicioDaSemana . ' - 7 days')); ?>">Semana
            Anterior</a> <!--BOTAOOOO-->
        <a
            href="?id_professor=<?php echo $id_professor; ?>&data=<?php echo date('d-m-Y', strtotime($inicioDaSemana . ' + 7 days')); ?>">Próxima
            Semana</a>

    </div>

    <table>
        <thead>
            <tr>
                <th>Horário</th>
                <th>Segunda</th>
                <th>Terça</th>
                <th>Quarta</th>
                <th>Quinta</th>
                <th>Sexta</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($horarios) && !empty($horarios)): ?>
                <?php foreach ($horarios as $hora => $dias): ?>
                    <?php
                    $horaInt = (int) explode(':', $hora)[0];
                    $minutoInt = (int) explode(':', $hora)[1];
                    $classe = ($horaInt < 14) ? 'manha' : 'noite';

                    // Formata o horário final corretamente
                    if (($horaInt == 9 && $minutoInt == 40) || ($horaInt == 20 && $minutoInt == 40)) {
                        // Saltos de 10 minutos
                        $hora_final = $horaInt;
                        $minuto_final = $minutoInt + 10;
                    } elseif ($horaInt >= 18) {
                        // Incrementos de 40 minutos após as 18:00
                        $hora_final = $horaInt;
                        $minuto_final = $minutoInt + 40;
                        if ($minuto_final >= 60) {
                            $minuto_final -= 60;
                            $hora_final++;
                        }
                    } else {
                        // Incrementos regulares de 50 minutos
                        $hora_final = $horaInt;
                        $minuto_final = $minutoInt + 50;
                        if ($minuto_final >= 60) {
                            $minuto_final -= 60;
                            $hora_final++;
                        }
                    }
                    $hora_formatada_final = str_pad($hora_final, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minuto_final, 2, '0', STR_PAD_LEFT);

                    // Ignora horários indesejados
                    if ($hora !== '09:40' && $hora !== '20:40' && $hora !== '12:20' && $hora !== '22:10') {
                    ?>
                        <tr class="<?php echo $classe; ?>">
                            <td><?php echo htmlspecialchars($hora) . " - " . htmlspecialchars($hora_formatada_final); ?></td>
                            <?php foreach (['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'] as $dia): ?>
                                <td>
                                    <?php if (isset($dias[$dia]) && !empty($dias[$dia])): ?>
                                        <?php foreach ($dias[$dia] as $agendamento): ?>
                                            <?php
                                            $color = '';
                                            if (isset($agendamento['idCor'])) {
                                                switch ($agendamento['idCor']) {
                                                    case 1:
                                                        $color = 'orange';
                                                        break;
                                                    case 2:
                                                        $color = '#03F132'; //verde
                                                        break;
                                                    case 3:
                                                        $color = 'yellow';
                                                        break;
                                                    case 4:
                                                        $color = '#00A8FF'; //azul
                                                        break;
                                                    case 5:
                                                        $color = 'red';
                                                        break;
                                                    default:
                                                        $color = 'gray';
                                                }
                                            }
                                            ?>
                                            <div class="agendamento" style="background-color: <?php echo $color; ?>;">
                                                <strong><?php echo htmlspecialchars($agendamento['professor_nome']); ?></strong>
                                                <?php if ($_GET["id_professor"] == $agendamento["idProf"]) {
                                                    echo '<form action="cancelarAgendamento.php" method="post">
                                                    <input type="hidden" name="id_agendamento" value=' .  $agendamento["id"]  . '
                                                    /><button type="submit">Cancelar</button>
                                                    </form>';
                                                } ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php } ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>





    </table>


</body>

</html>