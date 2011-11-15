<?php

/*
 * Copyright (c) 2011 Knut Ahlers <knut@ahlers.me>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */

class ErrorHandler {
  
  private function design_error($exception) {

      // these are our templates
      $traceline = "#%s %s(%s): %s(%s)";
      $msg = "Uncaught exception '%s' with message '%s' <br />in %s:%s";
      $trace_s = "Stack trace:\n%s\n  thrown in %s on line %s";

      // alter your trace as you please, here
      $trace = $exception->getTrace();
      foreach ($trace as $key => $stackPoint) {
          // I'm converting arguments to their type
          // (prevents passwords from ever getting logged as anything other than 'string')
          $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
      }

      // build your tracelines
      $result = array();
      foreach ($trace as $key => $stackPoint) {
          $result[] = sprintf(
              $traceline,
              $key,
              $stackPoint['file'],
              $stackPoint['line'],
              $stackPoint['function'],
              implode(', ', $stackPoint['args'])
          );
      }
      // trace always ends with {main}
      $result[] = '#' . ++$key . ' {main}';

      // write tracelines into main template
      $msg = sprintf(
          $msg,
          get_class($exception),
          $exception->getMessage(),
          $exception->getFile(),
          $exception->getLine()
      );
      $trace = sprintf(
          $trace_s,
          implode("\n", $result),
          $exception->getFile(),
          $exception->getLine()
      );

      // log or echo as you please
      return array(
        'msg' => $msg,
        'trace' => $trace
      );
  }
  
  static function handle_error($e) {
    header('HTTP/1.1 500 Internal Server Error');
    $exception = self::design_error($e);
    include(dirname(__file__) . '/../templates/internal/error.php');
  }
  
}
