<?php

class ZenValidatorTest extends SapphireTest
{

    private function Form()
    {
        $fields    = FieldList::create(TextField::create('Title'), TextField::create('Subtitle'));
        $actions    = FieldList::create(FormAction::create('submit', 'submit'));
        $validator    = ZenValidator::create();

        return Form::create(Controller::curr(), 'Form', $fields, $actions, $validator);
    }


    public function testSetGetRemoveContraint()
    {
        $zv = $this->Form()->getValidator();

        // set/get
        $zv->setConstraint('Title', Constraint_required::create());
        $this->assertTrue($zv->getConstraint('Title', 'Constraint_required') instanceof  Constraint_required);

        // remove
        $zv->removeConstraint('Title', 'Constraint_required');
        $this->assertTrue($zv->getConstraint('Title', 'Constraint_required') == null);
    }


    public function testSetGetRemoveContraints()
    {
        $zv = $this->Form()->getValidator();

        // set/get
        $zv->setConstraints(array(
            'Title' => array(
                Constraint_required::create(),
                Constraint_length::create('min', 4)
            ),
            'Subtitle' => Constraint_required::create()
        ));

        $this->assertTrue(count($zv->getConstraints('Title')) == 2);
        $this->assertTrue(count($zv->getConstraints('Subtitle')) == 1);

        // remove
        $zv->removeConstraints('Title');
        $zv->removeConstraints('Subtitle');

        $this->assertTrue(count($zv->getConstraints('Title')) == 0);
        $this->assertTrue(count($zv->getConstraints('Subtitle')) == 0);
    }


    public function testAddRequiredFields()
    {
        $zv = $this->Form()->getValidator();

        $zv->addRequiredFields(array(
            'Title',
            'Subtitle'
        ));

        $this->assertTrue($zv->getConstraint('Title', 'Constraint_required') instanceof  Constraint_required);
        $this->assertTrue($zv->getConstraint('Subtitle', 'Constraint_required') instanceof  Constraint_required);
    }


    public function testCustomMessages()
    {
        $zv = $this->Form()->getValidator();

        $titleMessage = 'Title custom message test';
        $subtitleMessage = 'Subtitle custom message test';

        $zv->addRequiredFields(array(
            'Title' => $titleMessage,
            'Subtitle' => $subtitleMessage
        ));

        $this->assertTrue($zv->getConstraint('Title', 'Constraint_required')->getMessage() == $titleMessage);
        $this->assertTrue($zv->getConstraint('Subtitle', 'Constraint_required')->getMessage() == $subtitleMessage);
    }


    public function testApplyAndDisableParsley()
    {
        $form    = $this->Form();
        $zv    = $form->getValidator();

        // test parsley is applied by default on set up
        $this->assertTrue($zv->parsleyIsEnabled());
        $this->assertContains('parsley', explode(' ', $form->extraClass()));

        // test parsley is disbaled/removed
        $zv->disableParsley();
        $this->assertFalse($zv->parsleyIsEnabled());
        $this->assertNotContains('parsley', explode(' ', $form->extraClass()));
    }
}
