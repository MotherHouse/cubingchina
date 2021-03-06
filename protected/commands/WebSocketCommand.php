<?php
use Ratchet\Session\SessionProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Server as Reactor;

Yii::import('application.websocket.*');

class WebSocketCommand extends CConsoleCommand {
	const PORT = 8080;
	const ADDRESS = '127.0.0.1';

	public function actionIndex() {
		$db = Yii::app()->db;
		$pdo = $db->getPdoInstance();
		$liveServer = new LiveServer();
		$session = new SessionProvider(
			$liveServer,
			new PdoSessionHandler($pdo, array(
				'lock_mode'=>PdoSessionHandler::LOCK_NONE,
			)),
			array(
				'name'=>'CUBINGCHINA_SID'
			)
		);
		Yii::getLogger()->autoDump = true;
		Yii::getLogger()->autoFlush = 1;
		$app = new HttpServer(new WsServer($session));
		$loop = new StreamSelectLoop();
		$socket = new Reactor($loop);
		$socket->listen(self::PORT, self::ADDRESS);
		$server = new IoServer($app, $socket, $loop);
		$server->run();
	}
}
