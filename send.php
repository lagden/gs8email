<?php
require_once dirname(__FILE__).'/vendor/autoload.php';

function param($param, $v = false) {
  return isset($_POST[$param]) ? $_POST[$param] : $v;
}

function isAjax() {
  return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
}

function buildMessage() {
  $templatePlain = implode('', [
    'Nome: {nome}', "\n",
    'Email: {email}', "\n",
    'Mensagem:', "\n",
    '{msg}'
  ]);

  $templateHTML = implode('', [
    '<div><b>Nome</b> {nome}</div>',
    '<div><b>Email</b> {email}</div>',
    '<div><b>Mensagem</b><br>',
    '<pre>{msg}</pre></div>'
  ]);

  $dados = [
    'nome'=> param('nome'),
    'email'=> param('email'),
    'msg'=> param('msg'),
  ];

  $plain = preg_replace_callback('/\{(.*?)\}/i', function($matches) use ($dados) {
    return $dados[$matches[1]];
  }, $templatePlain);

  $html = preg_replace_callback('/\{(.*?)\}/i', function($matches) use ($dados) {
    return $dados[$matches[1]];
  }, $templateHTML);

  return [$plain, $html];
}

$response = [
  'success' => false,
  'message' => 'Falha ao enviar'
];

$idade = param('idade');

if (isAjax() && $idade === false) {
  $nome = param('nome');
  $email = param('email');
  $from = [$email => $nome];
  $msg = buildMessage();
  $transport = \Swift_MailTransport::newInstance();
  $mailer = \Swift_Mailer::newInstance($transport);
  $message = \Swift_Message::newInstance()
    ->setSubject('[Contato] Site Grupo Super 8')
    ->setFrom($from)
    ->setReplyTo($from)
    ->setTo(['lagden@gmail.com'])
    ->setBody($msg[1], 'text/html')
    ->addPart($msg[0], 'text/plain');

  $result = $mailer->send($message);
  if ($result) {
    $response['success'] = true;
    $response['message'] = 'Enviado com sucesso';
  }
}

echo json_encode($response);
