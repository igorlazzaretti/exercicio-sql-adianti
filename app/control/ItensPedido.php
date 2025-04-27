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

class ItensPedido extends TPage
{
    private $datagrid;

    public function __construct()
    {
        parent::__construct();

        // Cria o datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);

        // Cria as colunas do datagrid
        $id_item = new TDataGridColumn('id_item', 'ID Item', 'center', '10%');
        $id_pedido = new TDataGridColumn('id_pedido', 'Nº Pedido', 'center', '15%');
        $produto = new TDataGridColumn('produto', 'Produto', 'left', '30%');
        $quantidade = new TDataGridColumn('quantidade', 'Quantidade', 'center', '15%');
        $preco_unitario = new TDataGridColumn('preco_unitario', 'Preço Unit.', 'right', '15%');
        $total = new TDataGridColumn('total', 'Total', 'right', '15%');

        // Formata as colunas de preço
        $preco_unitario->setTransformer(function ($value) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        });

        $total->setTransformer(function ($value) {
            return 'R$ ' . number_format($value, 2, ',', '.');
        });

        // Adiciona as colunas ao datagrid
        $this->datagrid->addColumn($id_item);
        $this->datagrid->addColumn($id_pedido);
        $this->datagrid->addColumn($produto);
        $this->datagrid->addColumn($quantidade);
        $this->datagrid->addColumn($preco_unitario);
        $this->datagrid->addColumn($total);

        // Cria o modelo do datagrid
        $this->datagrid->createModel();

        // Título da Lista
        $title = new TLabel('Tabela: Itens de Pedido', 'fa:shopping-cart');
        $title->setTip('Lista de Itens dos Pedidos');

        // Cria o painel e adiciona o datagrid
        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($title);

        $form = new TForm('form_itens_pedido');

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
            'CREATE TABLE Itens_Pedido (
                id_item INTEGER PRIMARY KEY AUTOINCREMENT,
                id_pedido INTEGER,
                id_produto INTEGER,
                quantidade INTEGER,
                preco_unitario REAL,
                FOREIGN KEY (id_pedido) REFERENCES Pedidos(id_pedido),
                FOREIGN KEY (id_produto) REFERENCES Produtos(id_produto)
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

            // Define a consulta SQL com JOIN para buscar o nome do produto
            $sql = "SELECT i.*, p.produto, 
                    (i.quantidade * i.preco_unitario) as total 
                    FROM Itens_Pedido i 
                    INNER JOIN Produtos p ON p.id_produto = i.id_produto 
                    ORDER BY i.id_pedido";

            $conn = TTransaction::get();
            $result = $conn->query($sql);

            // Adiciona os resultados ao datagrid
            foreach ($result as $row) {
                $object = new stdClass;
                $object->id_item = $row['id_item'];
                $object->id_pedido = $row['id_pedido'];
                $object->produto = $row['produto'];
                $object->quantidade = $row['quantidade'];
                $object->preco_unitario = $row['preco_unitario'];
                $object->total = $row['total'];

                $this->datagrid->addItem($object);
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
