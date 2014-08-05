<?php

namespace App\AdminModule;


use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Tracy\Debugger;


abstract class BaseForm extends Form
{

    /** @var \Nette\Database\Context */
    protected $db;

    /** @var \Nette\Database\Table\Selection */
    protected $selection;


    /**
     * @param Context $db
     * @param Selection $selection
     */
    public function __construct(Context $db, Selection $selection)
    {
        parent::__construct();

        $this->db = $db;
        $this->selection = $selection;

        $this->prepare();

        $this->addSubmit('submit', 'Uložit')
            ->getControlPrototype()
            ->class('btn btn-primary');

        $this->getElementPrototype()
            ->class('form-horizontal');

        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div class="widget-content nopadding"';
        $renderer->wrappers['pair']['container'] = 'div class="control-group"';
        $renderer->wrappers['label']['container'] = 'div class="control-label"';
        $renderer->wrappers['control']['container'] = 'div class="controls"';

    }


    /*
     * Prepare form buttons
     */
    abstract public function prepare();


    /**
     * Default form handler
     */
    public function process()
    {
        $values = $this->values;

        try {

            if (isset($values['id'])) {

                $id = $values['id'];
                unset($values['id']);

                $this->selection->wherePrimary($id)->update($values);

            } else {
                $this->selection->insert($values);
            }

        } catch (\PDOException $e) {
            $this->addError($e->getMessage());
            dump($e);
            Debugger::log($e);
        }
    }

}