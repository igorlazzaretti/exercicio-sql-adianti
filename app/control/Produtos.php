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

class Produtos extends TPage
{
    private $datagrid;

    public function __construct()
    {
        parent::__construct();

        // Cria o datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);

        // Cria as colunas do datagrid
        $id_produto = new TDataGridColumn('id_produto', 'ID', 'left', '10%');
        $produto = new TDataGridColumn('produto', 'Produto', 'left', '40%');
        $preco_unitario = new TDataGridColumn('preco_unitario', 'Preço Unitário', 'right', '25%');
        $quantidade_estoque = new TDataGridColumn('quantidade_estoque', 'Estoque', 'right', '25%');

        // Formata a coluna de preço
        $preco_unitario->setTransformer(function($value) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        });

        // Adiciona as colunas ao datagrid
        $this->datagrid->addColumn($id_produto);
        $this->datagrid->addColumn($produto);
        $this->datagrid->addColumn($preco_unitario);
        $this->datagrid->addColumn($quantidade_estoque);

        // Cria o modelo do datagrid
        $this->datagrid->createModel();

        // Título da Lista
        $title = new TLabel('Tabela: Produtos', 'fa:box');
        $title->setTip('Lista de Produtos em Estoque');

        // Cria o painel e adiciona o datagrid
        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($title);

        $form = new TForm('form_produtos');

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
            'CREATE TABLE Produtos (
                id_produto INTEGER PRIMARY KEY AUTOINCREMENT,
                produto TEXT,
                preco_unitario REAL,
                quantidade_estoque INTEGER
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

    public function onReload($param = null)
    {
        try {
            // Abre uma transação com o banco de dados
            TTransaction::open('sqlite');

            // Limpa o datagrid antes de adicionar novos itens
            $this->datagrid->clear();

            // Define a consulta SQL
            $sql = "SELECT * FROM Produtos ORDER BY id_produto";
            $conn = TTransaction::get();
            $result = $conn->query($sql);

            // Adiciona os resultados ao datagrid
            foreach ($result as $row) {
                $object = new stdClass;
                $object->id_produto = $row['id_produto'];
                $object->produto = $row['produto'];
                $object->preco_unitario = $row['preco_unitario'];
                $object->quantidade_estoque = $row['quantidade_estoque'];
                
                $this->datagrid->addItem($object);
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}