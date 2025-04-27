<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Dialog\TMessage;;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TText;
use Adianti\Wrapper\BootstrapFormBuilder;


class SQL7 extends TPage
{
    private $form;
    private $sql_entry;

    public function __construct()
    {
        parent::__construct();

        // Cria o Texto que fica a questão a ser resolvida
        $question = new TPanelGroup('Questão 7');

        $label = new TLabel('Receita Mensal: Calcule o total de vendas (soma de valor_total) agrupado por mês e ano.');
        $label->setFontSize('16px');

        $question->add($label);

        // Cria o formulários de entrada SQL
        $this->form = new BootstrapFormBuilder('form_SQLExecutor');
        $this->form->setFormTitle('Executar Comando SQL');

        // Cria o campo de entrada SQL
        $this->sql_entry = new TText('sql');
        $this->sql_entry->setSize('100%', 150); // height in pixels
        $this->sql_entry->setProperty('placeholder', 'Digite seu comando SQL aqui...');
        $this->sql_entry->style = 'font-family: monospace; white-space: pre;';

        // Adiciona campo no formulário
        $this->form->addFields([new TLabel('Comando SQL')], [$this->sql_entry]);

        // Cria botão "Executar"
        $this->form->addAction('Executar', new TAction([$this, 'onExecuteSQL']), 'fa:play green');
        $this->form->addAction('Resultado', new TAction([$this, 'onResult']), 'fa:dice blue');

        // Cria o formulário para exibir os resultados
        $panel = new TPanelGroup('Resultado SQL');
        $this->table = new TTable;
        $this->table->style = 'height: 23vh; overflow-y: auto; font-family: monospace; white-space: pre;';
        $this->table->width = '100%';
        $panel->add($this->table);
        $panel->addFooter('Fim do Resultado SQL');

        // Adiciona os formulários à página
        parent::add($question);
        parent::add($this->form);
        parent::add($panel);
    }

    public function onExecuteSQL($param)
    {
        try {
            TTransaction::open('sqlite');

            $sql = $param['sql'] ?? '';

            if (empty($sql)) {
                throw new Exception('O comando SQL está vazio');
            }

            $conn = TTransaction::get();
            $result = $conn->query($sql);

            if ($result) {
                $data = $result->fetchAll(PDO::FETCH_ASSOC);

                if (count($data) > 0) {
                    // Limpa a tabela antes de inserir novos dados
                    $this->table->clearChildren();

                    // Create text output
                    $output = "";

                    // Headers
                    $headers = array_keys($data[0]);
                    foreach ($headers as $header) {
                        $output .= str_pad($header, 20) . " | ";
                    }
                    $output .= "\n" . str_repeat("-", (count($headers) * 22)) . "\n";

                    // Data rows
                    foreach ($data as $row) {
                        foreach ($row as $value) {
                            $output .= str_pad(substr($value, 0, 19), 20) . " | ";
                        }
                        $output .= "\n";
                    }

                    // Cria uma única célula com o conteúdo
                    $row = $this->table->addRow();
                    $cell = $row->addCell($output);
                    $cell->style = 'white-space: pre;'; // Preserva formatação do texto

                } else {
                    new TMessage('info', 'A consulta não retornou resultados.');
                }
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onResult($param)
    {
        // Define a query SQL esperada
        $query = 'SELECT * FROM Clientes WHERE ativo = 1;';

        // Exibe a mensagem com a query formatada
        new TMessage('info', $query);
    }
}
