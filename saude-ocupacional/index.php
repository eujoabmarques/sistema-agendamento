<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber dados do formulário
    $nome = $_POST['nome'];
    $cnpj_cpf = $_POST['cnpj_cpf'];
    $telefone = $_POST['telefone'];
    $nome_contato = $_POST['nome_contato'];
    $email = $_POST['email'];
    $data = $_POST['data'];
    $horario = $_POST['horario'];

    // Conectar ao banco de dados SQLite
    $db = new SQLite3('agendamentos.db');

    // Verificar quantos agendamentos já existem para o horário das 10h às 10:30h
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM agendamentos WHERE data = :data AND horario BETWEEN "10:00" AND "10:30"');
    $stmt->bindValue(':data', $data, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row10 = $result->fetchArray(SQLITE3_ASSOC);

    // Verificar quantos agendamentos já existem para o horário das 16h às 16:30h
    $stmt = $db->prepare('SELECT COUNT(*) as count FROM agendamentos WHERE data = :data AND horario BETWEEN "16:00" AND "16:30"');
    $stmt->bindValue(':data', $data, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row16 = $result->fetchArray(SQLITE3_ASSOC);

    // Verificar se o horário solicitado está no intervalo permitido e dentro do limite de agendamentos
    if (($horario >= '10:00' && $horario <= '10:30' && $row10['count'] >= 6) || 
        ($horario >= '16:00' && $horario <= '16:30' && $row16['count'] >= 6)) {
        // Limite de agendamentos atingido para o horário específico
        echo "<script>alert('Limite de agendamentos para esse horário atingido.'); window.location.href='/sistema-agendamento/saude-ocupacional/index.html';</script>";
    } else {
        // Preparar a instrução SQL para inserção
        $stmt = $db->prepare('
            INSERT INTO agendamentos (nome, cnpj_cpf, telefone, nome_contato, email, data, horario)
            VALUES (:nome, :cnpj_cpf, :telefone, :nome_contato, :email, :data, :horario)
        ');

        // Associar os valores às variáveis da instrução SQL
        $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
        $stmt->bindValue(':cnpj_cpf', $cnpj_cpf, SQLITE3_TEXT);
        $stmt->bindValue(':telefone', $telefone, SQLITE3_TEXT);
        $stmt->bindValue(':nome_contato', $nome_contato, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':data', $data, SQLITE3_TEXT);
        $stmt->bindValue(':horario', $horario, SQLITE3_TEXT);

        // Executar a instrução SQL
        if ($stmt->execute()) {
            // Definir mensagem de sucesso e redirecionar
            echo "<script>alert('Agendamento registrado com sucesso.'); window.location.href='/sistema-agendamento/saude-ocupacional/index.html';</script>";
        } else {
            // Definir mensagem de erro e redirecionar
            echo "<script>alert('Falha ao registrar o agendamento.'); window.location.href='/sistema-agendamento/saude-ocupacional/index.html';</script>";
        }
    }

    // Fechar a conexão com o banco de dados
    $db->close();
}
?>
