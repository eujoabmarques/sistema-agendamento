<?php
$db = new SQLite3('agendamentos.db');

$db->exec('
    CREATE TABLE IF NOT EXISTS agendamentos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        cnpj_cpf TEXT NOT NULL,
        telefone TEXT NOT NULL,
        nome_contato TEXT,
        email TEXT NOT NULL,
        data TEXT NOT NULL,
        horario TEXT NOT NULL
    )
');

echo 'Banco de dados e tabela criados com sucesso.';
?>
