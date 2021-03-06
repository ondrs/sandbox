<?php

namespace App\AdminModule\Form;


use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;


abstract class BaseForm extends Form
{

    /** @var \Nette\Database\Context */
    protected $db;

    /** @var \Nette\Database\Table\Selection */
    protected $selection;

    /** @var array of callbacks */
    public $onBeforeProcess;
    public $onAfterProcess;

    public $onBeforeUpdate;
    public $onAfterUpdate;

    public $onBeforeInsert;
    public $onAfterInsert;


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

        // setup form rendering
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

        // make form and controls compatible with Twitter Bootstrap
        $this->getElementPrototype()->class('form-horizontal');
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
        /** @var ArrayHash $values */
        $values = $this->values;

        try {

            $this->onBeforeProcess($this, $values);

            if (isset($values->id)) {

                $this->onBeforeUpdate($this, $values);

                $arr = (array)$values;
                unset($arr['id']);

                $row = $this->selection
                    ->wherePrimary($values->id)
                    ->fetch();

                $row->update($arr);

                $this->onAfterUpdate($row, $this, $values);

            } else {

                $this->onBeforeInsert($this, $values);

                $row = $this->selection->insert($values);

                $this->onAfterInsert($row, $this, $values);
            }

            $this->onAfterProcess($row, $this, $values);

        } catch (\PDOException $e) {
            $this->addError($e->getMessage());
            dump($e);
            Debugger::log($e);
        }

    }

}
