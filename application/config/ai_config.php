<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| AI Interview Question Generator — API Configuration
|--------------------------------------------------------------------------
|
| ai_provider    : 'gemini' | 'none'
| ai_api_key     : Your Google Gemini API key. DO NOT commit to Git.
|                  Alternatively set environment variable: GEMINI_API_KEY
| ai_model       : Gemini model to use. Default: gemini-1.5-flash
| ai_enabled     : true to use AI generation; false to use fallback engine
| ai_timeout     : HTTP timeout in seconds for Gemini API calls
| ai_max_retries : Number of times to retry if AI returns invalid output
|
| HOW TO SET YOUR API KEY (choose one):
|  1. Set it directly below (local dev only, never commit to Git):
|     $config['ai_api_key'] = 'YOUR_KEY_HERE';
|
|  2. Set Windows/Linux environment variable GEMINI_API_KEY and leave
|     the value below empty — the library will read it automatically.
|
*/

$config['ai_provider']    = 'gemini';
$config['ai_api_key']     = 'AQ.Ab8RN6KctRaK-X2Peh5QOckw1gZa51f90Xw-RjiY4C35CKysUQ';
$config['ai_model']       = 'gemini-3.6-flash';
$config['ai_enabled']     = true;
$config['ai_timeout']     = 45;
$config['ai_max_retries'] = 2;

