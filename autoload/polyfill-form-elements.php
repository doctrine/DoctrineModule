<?php

declare(strict_types=1);

use DoctrineModule\Form\Element\ObjectMultiCheckboxV2Polyfill;
use DoctrineModule\Form\Element\ObjectMultiCheckboxV3Polyfill;
use DoctrineModule\Form\Element\ObjectRadioV2Polyfill;
use DoctrineModule\Form\Element\ObjectRadioV3Polyfill;
use DoctrineModule\Form\Element\ObjectSelectV2Polyfill;
use DoctrineModule\Form\Element\ObjectSelectV3Polyfill;
use Laminas\Form\Element\MultiCheckbox;

call_user_func(function () {
    $reflectionClass = new ReflectionClass(MultiCheckbox::class);
    $reflectionMethod = $reflectionClass->getMethod('getValueOptions');

    if (null === $reflectionMethod->getReturnType()) {
        // aliases for laminas-form ^2.0
        class_alias(ObjectSelectV2Polyfill::class, 'DoctrineModule\Form\Element\ObjectSelect');
        class_alias(ObjectRadioV2Polyfill::class, 'DoctrineModule\Form\Element\ObjectRadio');
        class_alias(ObjectMultiCheckboxV2Polyfill::class, 'DoctrineModule\Form\Element\ObjectMultiCheckbox');
    } else {
        // aliases for laminas-form ^3.0
        class_alias(ObjectSelectV3Polyfill::class, 'DoctrineModule\Form\Element\ObjectSelect');
        class_alias(ObjectRadioV3Polyfill::class, 'DoctrineModule\Form\Element\ObjectRadio');
        class_alias(ObjectMultiCheckboxV3Polyfill::class, 'DoctrineModule\Form\Element\ObjectMultiCheckbox');
    }
});
