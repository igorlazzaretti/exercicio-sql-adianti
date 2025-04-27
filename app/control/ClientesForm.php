<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TToast;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TModalForm;
use Adianti\Wrapper\BootstrapFormBuilder;

class ClientesForm extends TPage
{
    private $form;

    public function __construct()
    {
        parent::__construct();

        // Cria o formulário
        $this->form = new TModalForm('form_clientes');
        $this->form->setFormTitle('Cadastrar Cliente');

        // Campos do formulário
        $nome = new TEntry('nome');
        $email = new TEntry('email');
        $cidade = new TEntry('cidade');
        $estado = new TCombo('estado');
        $data_cadastro = new TDate('data_cadastro');
        $data_cadastro->setEditable(false); // Desabilita a edição manual do campo de data
        
        // Adiciona opções para o campo 'estado'
        $estado->addItems([
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AP' => 'Amapá',
            'AM' => 'Amazonas',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MT' => 'Mato Grosso',
            'MS' => 'Mato Grosso do Sul',
            'MG' => 'Minas Gerais',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PR' => 'Paraná',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RS' => 'Rio Grande do Sul',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'SC' => 'Santa Catarina',
            'SP' => 'São Paulo',
            'SE' => 'Sergipe',
            'TO' => 'Tocantins',
        ]);

        // Configurações adicionais
        $data_cadastro->setMask('dd/mm/yyyy');
        $data_cadastro->setDatabaseMask('yyyy-mm-dd');
        $data_cadastro->setValue(date('Y-m-d')); // Define a data atual como padrão

        // Adiciona os campos ao formulário
        $this->form->addRowField('Nome:', $nome, true);
        $this->form->addRowField('Email:', $email, true);
        $this->form->addRowField('Cidade:', $cidade, true);
        $this->form->addRowField('Estado:', $estado, true);
        $this->form->addRowField('', $data_cadastro, true);

        // Botões de ação
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save');
        $this->form->addFooterAction('Voltar', new TAction([$this, 'onBack']), 'fa:arrow-left');

        // Adiciona o formulário à página
        parent::add($this->form);
    }

    public function onSave($param)
    {
        try {
            TTransaction::open('sqlite'); // Abre uma transação com o banco de dados

            $this->form->validate(); // Valida os dados do formulário

            $data = $this->form->getData(); // Obtém os dados do formulário

            // Verifica se todos os campos obrigatórios estão preenchidos
            if (empty($data->nome) || empty($data->email) || empty($data->cidade) || empty($data->estado) || empty($data->data_cadastro)) {
                throw new Exception('Todos os campos são obrigatórios.');
            }

            $cliente = new Clientes;
            $cliente->fromArray((array) $data);
            $cliente->store(); // Armazena o objeto no banco de dados

            TTransaction::close(); // Fecha a transação

            // Coloca os dados de volta no formulário
            $this->form->setData($data);

            // Exibe uma mensagem de sucesso
            new TMessage('info', 'Cliente cadastrado com sucesso!', new TAction([$this, 'onBack']));

            // Exibe um toast de confirmação
            TToast::show('success', 'Cliente cadastrado com sucesso!', 'top right', 'fa:check-circle');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback(); // Desfaz a transação em caso de erro
        }
    }

    public function onBack()
    {
        AdiantiCoreApplication::gotoPage('ClientesList', 'onReload');
    }
}