<?php
/**
 * Created by Doniyor Mamatkulov.
 * User: d.mamatkulov
 * Date: 16.11.2018
 * Time: 9:05
 */

namespace app\controllers;


class MtUserRate {

    public function actionUserRate() {
        $this->view->title = Yii::$app->params['application_name'].' | '.Yii::t('app', 'Rate limit');
        $rLib = new MikrotikRouterAPI();
        $cookies = Yii::$app->request->cookies;
        $gear = $cookies->getValue('mtID');
        $l = new Library();
        $ip = $l->getIPbyID($gear);
        $credo = $l->getCredentials($ip);
        if ($rLib->connect($ip, $credo['login'], $credo['password'])) {
            $rLib->write('/ip/hotspot/user/profile/print');
            $READ = $rLib->read(false);
            $cValue = $rLib->parseResponse($READ);
            $rLib->disconnect();
        }
        $model = new MikrotikGear();
        if($model->load(Yii::$app->request->post())) {
            $rLib->debug = false;
            $sp = $model->userSpeed->rate;
            $ip = $l->getIPbyID($model->gear_ip);
            $credo = $l->getCredentials($ip);
            if ($rLib->connect($ip, $credo['login'], $credo['password'])) {
                $rLib->comm("/ip/hotspot/user/profile/set", array(
                    "name" => "nhup",
                    "rate-limit" => $sp,
                    "numbers" => 0,
                ));
                Yii::$app->session->setFlash('success', Yii::t('app', 'Settings successfully saved!'));
                $rLib->disconnect();
                return $this->redirect('user-rate');
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('app', 'Gear connection error!'));
            }
        }
        return $this->render('user-rate', compact('model', 'cValue'));
    }

}