<?php
session_start();

// Definir as credenciais do administrador
$admin_username = 'admin';
$admin_password = 'senha123';  // Substitua pela senha que você deseja usar

// Verificar se o usuário já está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Processar o formulário de login
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Verificar as credenciais do usuário
        if ($username === $admin_username && $password === $admin_password) {
            $_SESSION['loggedin'] = true;
            header("Location: admin.php");
            exit();
        } else {
            echo 'Credenciais incorretas.';
        }
    }
} else {
    // Processar ações de editar, excluir e baixar
    if (isset($_POST['action'])) {
        $db = new SQLite3('agendamentos.db');
        if ($_POST['action'] == 'delete') {
            $ids = $_POST['ids'];
            foreach ($ids as $id) {
                $db->exec("DELETE FROM agendamentos WHERE id = $id");
            }
        }
        if ($_POST['action'] == 'edit') {
            // Redirecionar para a página de edição com os IDs dos itens selecionados
            $ids = implode(',', $_POST['ids']);
            header("Location: edit.php?ids=$ids");
            exit();
        }
        if ($_POST['action'] == 'download') {
            $ids = $_POST['ids'];
            if (!empty($ids)) {
                // Gerar o arquivo CSV
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="agendamentos.csv"');

                // Abrir o buffer de saída
                $output = fopen('php://output', 'w');

                // Escrever o cabeçalho do CSV (organizado e fácil de abrir em planilhas)
                fputcsv($output, ['ID', 'Nome', 'CNPJ/CPF', 'Telefone', 'Nome do Contato', 'Email', 'Data', 'Horário'], ';');

                // Buscar os dados dos agendamentos selecionados
                foreach ($ids as $id) {
                    $result = $db->query("SELECT * FROM agendamentos WHERE id = $id");
                    $row = $result->fetchArray(SQLITE3_ASSOC);
                    if ($row) {
                        // Escrever cada linha no arquivo CSV (separando por ponto e vírgula para planilhas brasileiras)
                        fputcsv($output, [
                            $row['id'],
                            $row['nome'],
                            $row['cnpj_cpf'],
                            $row['telefone'],
                            $row['nome_contato'],
                            $row['email'],
                            $row['data'],
                            $row['horario']
                        ], ';'); // Use ";" como delimitador para compatibilidade com planilhas em português
                    }
                }

                // Fechar o buffer de saída
                fclose($output);
                exit();
            } else {
                echo "<script>alert('Nenhum agendamento selecionado para baixar.'); window.location.href='admin.php';</script>";
            }
        }
    }

    // Filtrar por data
    $date_filter = '';
    if (isset($_GET['filter_date'])) {
        $date_filter = $_GET['filter_date'];
    }

    $db = new SQLite3('agendamentos.db');
    $query = 'SELECT * FROM agendamentos';
    if ($date_filter) {
        $query .= " WHERE data = '$date_filter'";
    }
    $results = $db->query($query);

    echo '<h1>Agendamentos</h1>';
    echo '<form method="GET" action="admin.php">
            <label for="filter_date">Filtrar por data:</label>
            <input type="date" id="filter_date" name="filter_date" value="' . htmlspecialchars($date_filter) . '">
            <input type="submit" value="Filtrar">
        </form>';
    
    echo '<form method="POST" action="admin.php">
            <input type="checkbox" id="select_all" onclick="toggleSelectAll(this)">
            <label for="select_all">Selecionar Todos</label>
            <input type="submit" name="action" value="delete">
            <input type="submit" name="action" value="edit">
            <input type="submit" name="action" value="download">
            <table border="1">
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CNPJ/CPF</th>
                    <th>Telefone</th>
                    <th>Nome do Contato</th>
                    <th>Email</th>
                    <th>Data</th>
                    <th>Horário</th>
                </tr>';

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        echo '<tr>
                <td><input type="checkbox" name="ids[]" value="' . $row['id'] . '"></td>
                <td>' . $row['id'] . '</td>
                <td>' . $row['nome'] . '</td>
                <td>' . $row['cnpj_cpf'] . '</td>
                <td>' . $row['telefone'] . '</td>
                <td>' . $row['nome_contato'] . '</td>
                <td>' . $row['email'] . '</td>
                <td>' . $row['data'] . '</td>
                <td>' . $row['horario'] . '</td>
            </tr>';
    }

    echo '</table>
        </form>';
    echo '<a href="logout.php">Sair</a>';
}
?>
<style>
body{
    display: flex;
    justify-content: center;
    align-items: center;
    background: #00a700;
    flex-direction: column;
}
form{
    width: 90%;
    border: 1px solid;
    padding: 20px;
    border-radius: 10px;
    background: #fff;
}
table{
    width: 100%;
}
</style>
<!-- Formulário de Login -->
<?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
<form method="POST" action="admin.php">
    <label for="username">Usuário:</label>
    <input type="text" id="username" name="username" required>
    <br>
    <label for="password">Senha:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <input type="submit" value="Entrar">
</form>
<?php endif; ?>

<script>
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('input[name="ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
}
</script>
