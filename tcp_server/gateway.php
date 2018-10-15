<?php
use model\HLK;
use model\CB01;
use model\CB05;
use model\CB06;
use model\CB140A;

define('DISABLEXSSCHECK', true);
define('PLUGIN_NAME', 'yunlian');
define('YUNLIAN_DIR_PATH', __DIR__ . '/../');
define('DZ_ROOT', YUNLIAN_DIR_PATH . '/../../');
// 初始化 discuz 内核对象
require DZ_ROOT . '/class/class_core.php';
$discuz = C::app();
$discuz->init();

// 加载基于PSR0/4规范的类
require_once YUNLIAN_DIR_PATH . '/vendor/autoload.php';

// $LOG_FILENAME配置log文件名称
$LOG_FILENAME = "_sync";
// 加载配置
require_once YUNLIAN_DIR_PATH . '/cfg.inc.php';

// 加载类库
require_once YUNLIAN_DIR_PATH . '/lib/scurl.class.php';

require_once YUNLIAN_DIR_PATH.'/lib/wxapi.class.php';

require_once YUNLIAN_DIR_PATH.'/lib/wxpay.class.php';

require_once YUNLIAN_DIR_PATH . '/func.inc.php';

if ($_POST) {
    $postData = file_get_contents("php://input");
    $content = json_decode($postData, 1);
    LOG::DEBUG('swoole get post data :'.print_r($content,1));
    $imei = $content['imei'];
    if ($content['type'] == '5A5A' || $content['type'] == '5a5a') {
        switch ($content['data']['cb01_protocol']) {
            case '0202':
                $res = getGatewayData($imei);
                exec('php swoole.php ' . SWOOLE_IP . ' ' . SWOOLE_PORT . ' ' . $imei . ' ' . $res);
                break;
        }
    } elseif ($content['type'] == 'HLK') {
        $hlk = new HLK();
        switch ($content['data']['protocol']) {
            case '01':
                die(json_encode($hlk->login($imei, $content['data'])));
                break;

            case '12':
                $local_time = $hlk->location($imei, $content['data']);
                if($local_time){
                    update_distance($imei,$local_time);
                }
                break;

            case '16':
                $hlk->warning($imei, $content['data']);
                break;

            case '13':
                $hlk->information($imei, $content['data']);
                break;

            case '18':
                $hlk->LBSLocation($imei, $content['data']);
                break;

            default:
                break;

        }
    } elseif ($content['type'] == 'CB01') {
        $CB01 = new CB01();
        switch ($content['data']['cb01_protocol']) {
            case '0000':
                if ($CB01->baseData($imei, $content['data'])) {
                    sendPowerWarning($imei);
                    $res = $CB01->response($imei,'0000');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0001':
                $cb01_version = $CB01->versionData($imei, $content['data']);
                $res = $CB01->response($imei,'0001');
                exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                break;

            case '0002':
                $res = $CB01->sensorData($imei, $content['data']);
                $res = $CB01->response($imei,'0002');
                exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                break;

            case '0003':
                if ($CB01->lighterData($imei, $content['data'])) {
                    $res = $CB01->response($imei,'0003');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0004':
                if ($CB01->lighterVoltage($imei, $content['data'])) {
                    $res = $CB01->response($imei,'0004');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0005':
                if ($CB01->lighterCurrent($imei, $content['data'])) {
                    $res = $CB01->response($imei,'0005');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0006':
                if ($CB01->lighterLowestVoltage($imei, $content['data'])) {
                    $res = $CB01->response($imei,'0006');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0007':
                if ($CB01->monitorVoltage($imei, $content['data'])) {
                    $res = $CB01->response($imei,'0007');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0008':
                if ($CB01->monitorCurrent($imei, $content['data'])) {
                    $res = $CB01->response($imei,'0008');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0009':
                if ($CB01->monitorLowest($imei, $content['data'])) {
                    $res = $CB01->response($imei,'0009');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0a00':
            case '0A00':
                $res = $CB01->updateData($imei, $content['data']);
                exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                break;

            default:
                # code...
                break;
        }
    } elseif ($content['type'] == 'CB05') {
        $CB05 = new CB05();
        switch ($content['data']['cb01_protocol']) {
            case '0000':
                if ($CB05->baseData($imei,$content['data'])) {
                    $res = $CB05->response($imei,'0000');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0001':
                if ($CB05->versionData($imei,$content['data'])) {
                    $res = $CB05->response($imei,'0001');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0004':
                if ($CB05->lighterData($imei,$content['data'])) {
                    $res = $CB05->response($imei,'0004');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0a00':
            case '0A00':
                $res = $CB05->updateData($imei, $content['data']);
                exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                break;

            default:
                # code...
                break;
        }
    } elseif($content['type'] == 'CB06'){
        $CB06 = new CB06();
        switch ($content['data']['cb01_protocol']) {
            case '0000':
                if ($CB06->baseData($imei,$content['data'])) {
                    $res = $CB06->response($imei,'0000');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0001':
                if ($CB06->versionData($imei,$content['data'])) {
                    $res = $CB06->response($imei,'0001');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0003':
                if ($CB06->lighterVoltage($imei,$content['data'])) {
                    $res = $CB06->response($imei,'0003');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0004':
                if ($CB06->lighterCurrent($imei,$content['data'])) {
                    $res = $CB06->response($imei,'0004');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            // case '0a00':
            // case '0A00':
            //     $res = $CB05->updateData($imei, $content['data']);
            //     exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
            //     break;

            default:
                # code...
                break;
       }
    } elseif ($content['type'] == '140A') {
        $CB140A = new CB140A();
        switch ($content['data']['cb01_protocol']) {
            case '0000':
                if ($CB140A->baseData($imei,$content['data'])) {
                    $res = $CB140A->response($imei,'0000');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0001':
                if ($CB140A->versionData($imei,$content['data'])) {
                    $res = $CB140A->response($imei,'0001');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0002':
                if ($CB140A->testingData($imei,$content['data'])) {
                    $res = $CB140A->response($imei,'0002');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
            case '0003':
                if ($CB140A->lighterVoltage($imei,$content['data'])) {
                    $res = $CB140A->response($imei,'0003');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0004':
                if ($CB140A->lighterCurrent($imei,$content['data'])) {
                    $res = $CB140A->response($imei,'0004');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;

            case '0005':
                if ($CB140A->chargingData($imei,$content['data'])) {
                    $res = $CB140A->response($imei,'0005');
                    exec('php swoole.php ' . SWOOLE_IP. ' ' . SWOOLE_PORT . ' ' . $res['imei'] . ' ' . $res['data']);
                }
                break;
        }
    }
}
