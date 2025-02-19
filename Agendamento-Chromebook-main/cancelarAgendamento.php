<?php
require_once 'auth.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['id'])) {
    die('Você precisa estar logado para cancelar um agendamento.');
}

$conn = new mysqli("localhost", "root", "", "chromebook");
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if (isset($_POST['id_agendamento'])) {
    $id_agendamento = intval($_POST['id_agendamento']);
    $id_professor_logado = $_SESSION['id'];

    $sql = "SELECT * FROM agendamento WHERE id = ? AND id_professor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_agendamento, $id_professor_logado);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_sql = "DELETE FROM agendamento WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id_agendamento);
        if ($delete_stmt->execute()) {
            $data_atual = htmlspecialchars(date("d-m-Y"), ENT_QUOTES, 'UTF-8');
            echo "<script>
                    alert('" . htmlspecialchars('Agendamento cancelado com sucesso.', ENT_QUOTES, 'UTF-8') . "');
                    alert(".var_dump($delete_stmt).");
                    window.location.href = 'AgendaSemanal.php?id_professor=" . urlencode($id_professor_logado) . "&data=" . urlencode($data_atual) . "';
                  </script>";
        } else {
            echo htmlspecialchars('Erro ao cancelar o agendamento.', ENT_QUOTES, 'UTF-8');
        }
        $delete_stmt->close();
    } else {
        echo htmlspecialchars('Erro: Agendamento não encontrado ou você não tem permissão para cancelá-lo.', ENT_QUOTES, 'UTF-8');
    }
    $stmt->close();
} else {
    echo htmlspecialchars('Erro: ID do agendamento não informado.', ENT_QUOTES, 'UTF-8');
}

$conn->close();
