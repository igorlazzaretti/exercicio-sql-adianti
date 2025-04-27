<?php

/**
 * Model para a tabela Clientes
 * Autor: Igor Dossin Lazzaretti
 */  

use Adianti\Database\TRecord;

class Cliente extends TRecord
{
    const TABLENAME  = 'Clientes';   // nome da tabela no banco
    const PRIMARYKEY = 'id_cliente'; // chave primária
    const IDPOLICY   = 'serial';     // ID gerado automaticamente pelo banco (sqlite/autoincrement)

    // atributos da tabela
    protected $id_cliente;    // Chave primária (auto-incremento)
    protected $nome;          // Nome do cliente
    protected $email;         // Email do cliente
    protected $cidade;        // Cidade onde o cliente mora
    protected $estado;        // Estado onde o cliente mora
    protected $data_cadastro; // Data de cadastro do cliente

}
?>
