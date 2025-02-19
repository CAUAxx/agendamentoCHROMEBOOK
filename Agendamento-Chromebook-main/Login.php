<?php
session_start();

$conn = new mysqli("localhost", "root", "", "chromebook");
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    $sql = "SELECT id, nome, senha FROM professor WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nome, $senha_bd);
        $stmt->fetch();

        // Verificar se a senha no banco ainda é "faeterj123" sem estar criptografada
        if ($senha_bd === "faeterj123") {
            $_SESSION['id'] = $id;
            $_SESSION['nome'] = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
            header("Location: /Agendamento-Chromebook-main/MudarSenha.php?id_professor=$id");
            exit();
        }

        // Verificar a senha criptografada
        if (password_verify($senha, $senha_bd)) {
            $_SESSION['id'] = $id;
            $_SESSION['nome'] = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
            header("Location: /Agendamento-Chromebook-main/AgendaSemanal.php?id_professor=$id");
            exit();
        } else {
            echo "<script>
                    alert('" . htmlspecialchars('Senha incorreta!', ENT_QUOTES, 'UTF-8') . "');
                    window.location.href = 'Login.html#popup1';
                  </script>";
        }
    } else {
        echo "<script>
                alert('" . htmlspecialchars('Email incorreto!', ENT_QUOTES, 'UTF-8') . "');
                window.location.href = 'Login.html#popup1';
              </script>";
    }

    $stmt->close();
}

$conn->close();
