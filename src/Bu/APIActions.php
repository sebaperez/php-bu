<?php

  namespace Bu;

  trait APIActions
  {
      public function actionAffectsExistingObject($action)
      {
          return $action !== self::ACTION_ADD();
      }

      public function getAction()
      {
          return $this->getParsedMethod()[1];
      }

      public static function isValidAction($action = "")
      {
          return in_array($action, self::VALID_ACTIONS());
      }

      public function getActionFunction($action)
      {
          $ACTION_FUNCTION = [
          self::ACTION_ADD() => function ($classname, $parameters) {
              $validationErrors = $classname::validate($parameters);
              if (count($validationErrors) !== 0) {
                  return $this->getResponseError(self::API_ERROR_VALIDATION(), $validationErrors);
              } else {
                  $object = $classname::add($parameters);
                  if ($object) {
                      return $this->getResponseSuccess($object->getValues());
                  } else {
                      return $this->getResponseError(self::API_ERROR_INTERNAL_ERROR());
                  }
              }
          },
          self::ACTION_DELETE() => function ($classname, $parameters) {
              $object = $this->getObject($classname, $parameters);
              if ($object) {
                  if ($object->delete()) {
                      return $this->getResponseSuccess($object->getValues());
                  } else {
                      return $this->getResponseError(self::API_ERROR_INTERNAL_ERROR());
                  }
              } else {
                  return $this->getResponseError(self::API_ERROR_FORBIDDEN());
              }
          },
          self::ACTION_VIEW() => function ($classname, $parameters) {
              $object = $this->getObject($classname, $parameters);
              if ($object) {
                  return $this->getResponseSuccess($object->getValues());
              } else {
                  return $this->getResponseError(self::API_ERROR_FORBIDDEN());
              }
          },
          self::ACTION_EDIT() => function ($classname, $parameters) {
              $object = $this->getObject($classname, $parameters);
              if ($object) {
                  $validationErrors = $classname::validateFields($parameters);
                  if (count($validationErrors) !== 0) {
                      return $this->getResponseError(self::API_ERROR_VALIDATION(), $validationErrors);
                  } else {
                      $editableFields = $classname::getEditableFields();
                      foreach ($parameters as $field => $value) {
                          if (in_array($field, $editableFields)) {
                              if (! $object->update($field, $value)) {
                                  return $this->getResponseError(self::API_ERROR_INTERNAL_ERROR());
                              }
                          }
                      }
                      $object = $this->getObject($classname, $parameters);
                      return $this->getResponseSuccess($object->getValues());
                  }
              } else {
                  return $this->getResponseError(self::API_ERROR_FORBIDDEN());
              }
          },
          self::ACTION_LIST() => function ($classname, $parameters) {
          }
        ];

          return $ACTION_FUNCTION[$action];
      }
  }
