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

/**
 * Base class for any handler used in urls.php
 * 
 * @author Knut Ahlers <knut@ahlers.me>
 */
class HttpHandler {
  
  /**
   * Short call to do a redirect to a new URL
   * 
   * @param string $url The full URL used in the redirect
   * @param int $code May be 301 (permanent redirect) or 302 (temporary redirect)
   */
  protected function redirect($url, $code = 302) {
    if($code == 301) {
      header("HTTP/1.1 301 Moved Permanently");
    } else {
      header("HTTP/1.1 302 Moved Temporarily");
    }
  }
  
  /**
   * Wrapper for template loading
   *
   * @param string $template Name of the template to execute
   */ 
  protected function do_layout($template) {
    tpl::load($template);
  }
  
  /**
   * Handler for GET requests
   * 
   * @param array $params Selections from the url defined in urls.php are passed to this
   * @throws HttpResponderException When not implemented in child class
   */
  public function get($params) { throw new HttpResponderException('Method GET not implemented for ' . get_class($this)); }
  
  /**
   * Handler for HEAD requests
   *
   * @param array $params Selections from the url defined in urls.php are passed to this    
   * @throws HttpResponderException When not implemented in child class
   */
  public function head($params) { throw new HttpResponderException('Method HEAD not implemented for ' . get_class($this)); }
  
  /**
   * Handler for POST requests
   * 
   * @param array $params Selections from the url defined in urls.php are passed to this
   * @throws HttpResponderException When not implemented in child class
   */
  public function post($params) { throw new HttpResponderException('Method POST not implemented for ' . get_class($this)); }


  /**
   * Handler for CLI requests
   * 
   * @param array $params Selections from the url defined in urls.php are passed to this
   * @throws HttpResponderException When not implemented in child class
   */
  public function cli($params) { throw new HttpResponderException('Method CLI not implemented for ' . get_class($this)); }
}

class HttpResponderException extends Exception {}
