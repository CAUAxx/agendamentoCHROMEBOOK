<?php
ob_start();
require_once 'auth.php';
@require 'Login.php';
ob_end_clean();

date_default_timezone_set("America/Sao_Paulo");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chromebook";

$data = htmlspecialchars($_POST["data"], ENT_QUOTES, 'UTF-8');
if (isset($_POST['horarios']) && !empty($_POST['horarios'])) 
    $horarios = array_map('htmlspecialchars', explode(",", $_POST["horarios"]));
else
    $horarios=NULL;

$preferencia = intval($_POST['preferencia']);
$cores = [1, 2, 3, 4, 5];
$id_professor = $_SESSION['id'];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$agendados = 0;
$quantidade = 0;

if(!empty($horarios) && !empty($data)) {
    foreach ($horarios as $hora) {
        foreach ($cores as $cor) {
            $check_sql = "SELECT * FROM agendamento WHERE idCor = ? AND data = ? AND horario = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("iss", $cor, $data, $hora);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows === 0) {
                $insert_sql = "INSERT INTO agendamento (id_professor, data, horario, idCor) VALUES (?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("issi", $id_professor, $data, $hora, $cor);
    
                if ($insert_stmt->execute()) {
                    $quantidade++;
                    $agendados++;
                    echo htmlspecialchars("Agendamento realizado para o horário $hora com a cor $cor\n", ENT_QUOTES, 'UTF-8');
                } else {
                    echo htmlspecialchars("Erro ao inserir agendamento: " . $conn->error . "\n", ENT_QUOTES, 'UTF-8');
                }
                $insert_stmt->close();
            }
    
            $stmt->close();
    
            if ($agendados >= $preferencia) {
                $agendados = 0;
                break;
            }
        }
    }
    
    if ($quantidade === 0) {
        echo htmlspecialchars('Não foi possível fazer nenhum agendamento. Todos os horários estão ocupados.', ENT_QUOTES, 'UTF-8');
    } else {
        echo htmlspecialchars("$quantidade agendamento(s) realizado(s) com sucesso.", ENT_QUOTES, 'UTF-8');
    }
    
    $conn->close();
    
} else {
    http_response_code(400);
}


