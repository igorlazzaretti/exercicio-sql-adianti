<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;
use Adianti\Widget\Dialog\TToast;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;

class Clientes extends TPage
{
    private $datagrid;

    public function __construct()
    {
        parent::__construct();

        // Cria o datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);

        // Cria as colunas do datagrid
        $id_cliente = new TDataGridColumn('id_cliente', 'Nro', 'left', '10%');
        $nome       = new TDataGridColumn('nome', 'Nome', 'left', '25%');
        $email      = new TDataGridColumn('email', 'Email', 'left', '25%');
        $cidade     = new TDataGridColumn('cidade', 'Cidade', 'left', '20%');
        $estado     = new TDataGridColumn('estado', 'Estado', 'left', '10%');
        $data_cadastro = new TDataGridColumn('data_cadastro', 'Data Cadastro', 'left', '20%');

        // Adiciona as colunas ao datagrid
        $this->datagrid->addColumn($id_cliente);
        $this->datagrid->addColumn($nome);
        $this->datagrid->addColumn($email);
        $this->datagrid->addColumn($cidade);
        $this->datagrid->addColumn($estado);
        $this->datagrid->addColumn($data_cadastro);

        // Cria o modelo do datagrid
        $this->datagrid->createModel();

        // Título da Lista
        $title = new TLabel('Tabela: Clientes', 'fa:users');
        $title->setTip('Lista de Clientes Cadastrados na Tabela: Clientes');

        // Cria o painel e adiciona o datagrid
        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($title);

        $form = new TForm('form_clientes');

        // Organiza o conteúdo da página
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        $vbox->add($form);
        parent::add($vbox);

        // Query com SQL que criou a tabela
        $panel_query = new TPanelGroup('Query que cria esta tabela');

        // Create a label with the SQL query - fixing constructor
        $query_label = new TLabel(
            'CREATE TABLE Clientes (
                id_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT,
                email TEXT,
                cidade TEXT,
                estado TEXT,
                data_cadastro DATE
            );'
        );

        // Set the query text to the label with pre formatting
        $query_label->setValue("<pre>" . $query_label->getValue() . "</pre>");
        $query_label->setSize('100%');

        // Add the label to the panel
        $panel_query->add($query_label);

        // Add panel to vbox instead of directly to page
        $vbox->add($panel_query);

        // onReload é chamado para carregar os dados do banco de dados
        $this->onReload();
    }

    // Método para recarregar a lista de clientes
    public function onReload($param = null)
    {
        try {
            // Abre uma transação com o banco de dados
            TTransaction::open('sqlite'); // Substitua 'sqlite' pelo nome correto da conexão

            // Limpa o datagrid antes de adicionar novos itens
            $this->datagrid->clear();

            // Obtém todos os registros da tabela Clientes usando a model
            $clientes = Cliente::all(); // Certifique-se de que a model Cliente está configurada corretamente

            // Adiciona cada cliente ao datagrid
            foreach ($clientes as $cliente) {
                $this->datagrid->addItem($cliente);
            }

            // Fecha a transação
            TTransaction::close();
        } catch (Exception $e) {
            // Em caso de erro, exibe a mensagem e desfaz a transação
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
