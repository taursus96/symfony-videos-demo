<?php

namespace AppBundle\Util;

use Symfony\Component\Form\Form;

class FormErrors
{
    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new FormErrors();
        }

        return self::$instance;
    }

    public function getErrorMessages(Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }
}
