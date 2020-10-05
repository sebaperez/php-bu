<?php

  namespace Bu;

  trait APIConst
  {
      public static function API_STATUS_ERROR()
      {
          return "error";
      }

      public static function API_STATUS_SUCCESS()
      {
          return "success";
      }

      public static function API_ERROR_INVALID_CLASSNAME()
      {
          return "API_ERROR_INVALID_CLASSNAME";
      }

      public static function API_ERROR_INVALID_ACTION()
      {
          return "API_ERROR_INVALID_ACTION";
      }

      public static function API_ERROR_INVALID_PARAMETERS()
      {
          return "API_ERROR_INVALID_PARAMETERS";
      }

      public static function API_ERROR_FORBIDDEN()
      {
          return "API_ERROR_FORBIDDEN";
      }

      public static function API_ERROR_INTERNAL_ERROR()
      {
          return "API_ERROR_INTERNAL_ERROR";
      }

      public static function API_ERROR_VALIDATION()
      {
          return "API_ERROR_VALIDATION";
      }

      public static function API_OUTPUT_JSON()
      {
          return "json";
      }

      public static function ACTION_ADD()
      {
          return "add";
      }

      public static function ACTION_DELETE()
      {
          return "delete";
      }

      public static function ACTION_VIEW()
      {
          return "view";
      }

      public static function ACTION_EDIT()
      {
          return "edit";
      }
      
      public static function ACTION_LIST()
      {
          return "list";
      }

      public static function VALID_ACTIONS()
      {
          return [
                self::ACTION_ADD(),
                self::ACTION_DELETE(),
                self::ACTION_VIEW(),
                self::ACTION_EDIT(),
                self::ACTION_LIST()
              ];
      }

      public static function API_MAP()
      {
          return [];
      }

      public static function SESSION_CLASS()
      {
          return "Bu\DefaultClass\Session";
      }
      public static function USER_CLASS()
      {
          return "Bu\DefaultClass\User";
      }
      public static function ACCOUNT_CLASS()
      {
          return "Bu\DefaultClass\Account";
      }
  }
