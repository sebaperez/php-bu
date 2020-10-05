<?php

  namespace Bu;

  trait APIResponse
  {
      public function getResponseError($code, $msg = null)
      {
          return $this->getResponse(self::API_STATUS_ERROR(), $code, $msg);
      }

      public function getResponseSuccess($msg)
      {
          return $this->getResponse(self::API_STATUS_SUCCESS(), null, $msg);
      }

      public function getResponse($status, $code = null, $msg)
      {
          $data = [ "status" => $status ];
          if ($code) {
              $data["code"] = $code;
          }
          if ($msg) {
              $data["data"] = $msg;
          }
          return $data;
      }
  }
