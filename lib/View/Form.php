<?php

namespace Defiant\View;

class Form extends \Defiant\Model\ModelAccessor {
  public function getInstanceFields() {
    return static::getDefinedFields();
  }

  public function getValidatedData(\Defiant\Http\Request $request) {
    $fields = static::getInstanceFields();
    $data = [];
    $errors = [];

    foreach ($fields as $field) {
      $fieldName = $field->getName();
      try {
        $data[$fieldName] = $this->getValidatedValue($request, $field);
      } catch(\Defiant\View\ValidationError $e) {
        $errors[] = $e;
        $data[$fieldName] = $this->getRawValue($request, $field);
      }
    }

    if (sizeof($errors) > 0) {
      throw new \Defiant\View\ValidationErrorCollection($errors, $data);
    }

    return $data;
  }

  public function getRawValue(
    \Defiant\Http\Request $request,
    \Defiant\Model\Field $field
  ) {
    return $request->getBodyParam($field->getName(), null);
  }

  public function getValidatedValue(
    \Defiant\Http\Request $request,
    \Defiant\Model\Field $field
  ) {
    $fieldName = $field->getName();
    $value = $this->getRawValue($request, $field);

    if (!$field->isNull() && !$value) {
      throw new \Defiant\View\ValidationError(sprintf('Field %s is required', $fieldName), $fieldName);
    }

    $field->validateValue($value);
    return $value;
  }
}
