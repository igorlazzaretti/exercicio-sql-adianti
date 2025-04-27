<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Database\TDatabase;
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
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;

class ClientesList extends TPage
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

        // Cria as ações do datagrid
        $action1 = new TDataGridAction([$this, 'onView'], [
            'id_cliente' => '{id_cliente}',
            'nome'       => '{nome}',
            'email'      => '{email}',
            'cidade'     => '{cidade}',
            'estado'     => '{estado}',
            'data_cadastro' => '{data_cadastro}'
        ]);

        $action2 = new TDataGridAction([$this, 'onDelete'], [
            'id_cliente' => '{id_cliente}',
            'nome'       => '{nome}',
        ]);

        $action3 = new TDataGridAction([$this, 'onEdit'], [
            'id_cliente' => '{id_cliente}',
            'nome'       => '{nome}',
        ]);

        // Apresentação personalizada dos botões
        $action1->setUseButton(TRUE);
        $action2->setUseButton(TRUE);
        $action3->setUseButton(TRUE);

        // Adiciona as ações ao datagrid
        $this->datagrid->addAction($action1, 'Ver', 'fa:search blue');
        $this->datagrid->addAction($action2, 'Excluir', 'fa:trash red');
        $this->datagrid->addAction($action3, 'Editar', 'fa:edit green');

        // Cria o modelo do datagrid
        $this->datagrid->createModel();

        // Popula os dados no datagrid
        try {
            TTransaction::open('sqlite');
            $conn = TTransaction::get();

            $result = $conn->query('SELECT id_cliente, nome, email, cidade, estado, data_cadastro FROM clientes ORDER BY id_cliente');

            foreach ($result as $row) {
                $item = new StdClass;
                $item->id_cliente = $row['id_cliente'];
                $item->nome       = $row['nome'];
                $item->email      = $row['email'];
                $item->cidade     = $row['cidade'];
                $item->estado     = $row['estado'];
                $item->data_cadastro = $row['data_cadastro'];
                $this->datagrid->addItem($item);
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }

        // Botão para cadastrar novo cliente
        $button = new TButton('cadastrar_novo_cliente');
        $button->setLabel('Cadastrar Cliente');
        $button->setImage('fa:plus green');
        $button->setAction(new TAction([$this, 'onCreateCliente']), 'Cadastrar Novo Cliente');

        // Cria o painel e adiciona o datagrid
        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addHeaderWidget(THBox::pack($button));
        $panel->addFooter('Lista de Clientes');
        $form = new TForm('form_clientes');
        $form->setFields([$button]);

        // Organiza o conteúdo da página
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        $vbox->add($form);
        parent::add($vbox);
    }

    // Método para recarregar a lista de clientes
    public function onReload($param = null)
    {
        // Cria o modelo do datagrid
        $this->datagrid->clear();

        // Popula os dados no datagrid novamente
        try {
            TTransaction::open('sqlite'); 
            $conn = TTransaction::get();

            $result = $conn->query('SELECT id_cliente, nome, email, cidade, estado, data_cadastro FROM clientes ORDER BY id_cliente');

            foreach ($result as $row) {
                $item = new StdClass;
                $item->id_cliente = $row['id_cliente'];
                $item->nome       = $row['nome'];
                $item->email      = $row['email'];
                $item->cidade     = $row['cidade'];
                $item->estado     = $row['estado'];
                $item->data_cadastro = $row['data_cadastro'];
                $this->datagrid->addItem($item);
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    // Ações de visualizar, excluir e editar
    public function onView($param)
    {
        // Exemplo de exibição dos detalhes do cliente
        TMessage::show('Visitar Cliente ' . $param['nome']);
    }

    public function onDelete($param)
    {
        // Excluir cliente
        try {
            TTransaction::open('sqlite');
            $cliente = new Clientes($param['id_cliente']);
            $cliente->delete();
            TTransaction::close();
            $this->onReload();
            new TMessage('info', 'Cliente excluído com sucesso!');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onEdit($param)
    {
        // Exemplo de redirecionamento para editar o cliente
        TApplication::loadPage('ClienteForm', 'onEdit', $param);
    }

    public function onCreateCliente()
    {
        $createYes = new TAction([$this, 'onCreateClienteYes']);

        new TQuestion('Deseja cadastrar um novo cliente?', $createYes);
    }
    public function onCreateClienteYes()
    {
        AdiantiCoreApplication::gotoPage('ClientesForm', 'onEdit', ['id_cliente' => 'new']);
    }
}
