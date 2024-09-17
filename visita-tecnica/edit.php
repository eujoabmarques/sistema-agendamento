<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: admin.php");
    exit();
}

// Conectar ao banco de dados
$db = new SQLite3('agendamentos.db');

// Obter os IDs dos agendamentos a serem editados
$ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];
$ids_placeholder = implode(',', array_fill(0, count($ids), '?'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Atualizar os agendamentos
    foreach ($ids as $id) {
        $nome = $_POST["nome_$id"];
        $cnpj_cpf = $_POST["cnpj_cpf_$id"];
        $telefone = $_POST["telefone_$id"];
        $nome_contato = $_POST["nome_contato_$id"];
        $email = $_POST["email_$id"];
        $data = $_POST["data_$id"];
        $horario = $_POST["horario_$id"];
        
        $db->exec("UPDATE agendamentos SET nome='$nome', cnpj_cpf='$cnpj_cpf', telefone='$telefone', nome_contato='$nome_contato', email='$email', data='$data', horario='$horario' WHERE id=$id");
    }
    header("Location: admin.php");
    exit();
}

// Buscar os agendamentos para edição
$query = "SELECT * FROM agendamentos WHERE id IN ($ids_placeholder)";
$stmt = $db->prepare($query);
foreach ($ids as $index => $id) {
    $stmt->bindValue($index + 1, $id, SQLITE3_INTEGER);
}
$results = $stmt->execute();

echo '<h1>Editar Agendamentos</h1>';
echo '<form method="POST" action="edit.php?ids=' . htmlspecialchars(implode(',', $ids)) . '">';

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $id = $row['id'];
    echo "<h2>Agendamento ID $id</h2>";
    echo '<label>Nome:</label>
          <input type="text" name="nome_' . $id . '" value="' . htmlspecialchars($row['nome']) . '">
          <br>
          <label>CNPJ/CPF:</label>
          <input type="text" name="cnpj_cpf_' . $id . '" value="' . htmlspecialchars($row['cnpj_cpf']) . '">
          <br>
          <label>Telefone:</label>
          <input type="text" name="telefone_' . $id . '" value="' . htmlspecialchars($row['telefone']) . '">
          <br>
          <label>Nome do Contato:</label>
          <input type="text" name="nome_contato_' . $id . '" value="' . htmlspecialchars($row['nome_contato']) . '">
          <br>
          <label>Email:</label>
          <input type="email" name="email_' . $id . '" value="' . htmlspecialchars($row['email']) . '">
          <br>
          <label>Data:</label>
          <input type="date" name="data_' . $id . '" value="' . htmlspecialchars($row['data']) . '">
          <br>
          <label>Horário:</label>
          <input type="time" name="horario_' . $id . '" value="' . htmlspecialchars($row['horario']) . '">
          <br><br>';
}

echo '<input type="submit" value="Atualizar">
      </form>';
echo '<a href="admin.php">Voltar</a>';
?>
