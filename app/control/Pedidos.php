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

class Pedidos extends TPage
{
    private $datagrid;

    public function __construct()
    {
        parent::__construct();

        // Cria o datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);

        // Cria as colunas do datagrid
        $id_pedido    = new TDataGridColumn('id_pedido',    'ID Pedido',   'center', '15%');
        $id_cliente   = new TDataGridColumn('id_cliente',   'ID Cliente',  'center', '15%');
        $cliente_nome = new TDataGridColumn('cliente_nome', 'Cliente',     'left',   '30%');
        $data_pedido  = new TDataGridColumn('data_pedido',  'Data Pedido', 'center', '20%');
        $valor_total  = new TDataGridColumn('valor_total',  'Valor Total', 'right',  '20%');

        // Formata a coluna de data
        $data_pedido->setTransformer(function ($value) {
            return date('d/m/Y', strtotime($value));
        });

        // Formata a coluna de valor
        $valor_total->setTransformer(function ($value) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        });

        // Adiciona as colunas ao datagrid
        $this->datagrid->addColumn($id_pedido);
        $this->datagrid->addColumn($id_cliente);
        $this->datagrid->addColumn($cliente_nome);
        $this->datagrid->addColumn($data_pedido);
        $this->datagrid->addColumn($valor_total);

        // Cria o modelo do datagrid
        $this->datagrid->createModel();

        // Título da Lista
        $title = new TLabel('Tabela: Pedidos', 'fa:shopping-cart');
        $title->setTip('Lista de Pedidos');

        // Cria o painel e adiciona o datagrid
        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($title);

        $form = new TForm('form_pedidos');

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
            'CREATE TABLE Pedidos (
                id_pedido INTEGER PRIMARY KEY AUTOINCREMENT,
                id_cliente INTEGER,
                data_pedido DATE,
                valor_total REAL,
                FOREIGN KEY (id_cliente) REFERENCES Clientes(id_cliente)
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
            TTransaction::open('sqlite');

            $this->datagrid->clear();

            // Consulta SQL com JOIN para obter o nome do cliente
            $sql = "SELECT p.*, c.nome as cliente_nome 
                    FROM Pedidos p 
                    LEFT JOIN Clientes c ON c.id_cliente = p.id_cliente 
                    ORDER BY p.id_pedido";

            $conn = TTransaction::get();
            $result = $conn->query($sql);

            foreach ($result as $row) {
                $object = (object) $row;
                $this->datagrid->addItem($object);
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
