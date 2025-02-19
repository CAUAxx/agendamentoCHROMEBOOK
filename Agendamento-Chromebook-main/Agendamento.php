<?php
@require 'Login.php';
require_once 'auth.php';

date_default_timezone_set("America/Sao_Paulo");

$id = $_SESSION['id'];
$actualDate = date("d-m-Y");

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/StyleAgendamento.css">
    <script type="text/javascript" src="main.js"></script>
    <title>Agendamento</title>
</head>

<body class="container">

    <section class="conteudo">

        <div class="calendario">

            <a class="btn-ant" id="btn_prev"><img src="https://i.imgur.com/wrwDSRQ.png" /></a>

            <h2 id="mes" style="color: white;">Mês</h2>
            <h2 id="ano" style="color: white;">Ano</h2>

            <a class="btn-pro" id="btn_next"><img src="https://i.imgur.com/4NINDmb.png" /></a>

            <table>
                <thead>

                    <tr>
                        <td>Dom</td>
                        <td>Seg</td>
                        <td>Ter</td>
                        <td>Qua</td>
                        <td>Qui</td>
                        <td>Sex</td>
                        <td>Sáb</td>
                    </tr>
                </thead>

                <tbody id="dias">

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>

                </tbody>
            </table>

        </div>

    </section>
    <br>
    <section class="quadrado">
        <h2 style="color: #1B98E0;">Escolha seu Horário!</h2>


        <section class="secao-turno">

            <div id="turno">

                <p id="manha">Manhã</p>
                <p id="noite">Noite</p>

            </div>

        </section>

        <section class="horas">
        </section>

        <section class="quantidade">
            <h3 style="margin-left:20px; " id="servico">Quantidade de Caixas:</h3>
            <select id="preferencia">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select><br>
        </section>
        <input type="hidden" id="redirect" value="<?php echo "?id_professor=$id&data=$actualDate" ?>">
        <br><br>
        <input type="submit" id="submit" value="Reservar">

    </section>

</body>

</html>