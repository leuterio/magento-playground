<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/SmashingMagazine/
 *                     LogProductUpdate/Model/Observer.php
 */
class Resultate_PaymentDeadline_Model_Observer{

  public function cancelUnpaidOrders()
	{
    $selectedStatusIn = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_status_in');
    $selectedStatusOut = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_status_out');
    $selectedDays = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_day');
    $selectedTimeUnformated = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_time');
    $selectedTime = str_replace(',', ':', $selectedTimeUnformated); //formatando a hora para hh:mm:ss
    $selectedIsEnabled = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_enable');
    $orderCollection = Mage::getResourceModel('sales/order_collection');


    /*testing variables
    Mage::log('Status entrada: '.$selectedStatusIn);
    Mage::log('Status saída: '.$selectedStatusOut);
    Mage::log('Dias: '.$selectedDays);
    Mage::log('Horas: '.$selectedTime);
    Mage::log('Habilitado: '.$selectedIsEnabled);
    */

    if($selectedIsEnabled == '1'){

      $orderCollection
              ->addFieldToFilter('status', $selectedStatusIn)
              ->addFieldToFilter('created_at', array(
                'lt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'$selectedDays $selectedTime'  DAY_SECOND)")))
              ->getSelect()
              ->order('entity_id')
              ->limit(10)
      ;

      $orders ="";
      foreach($orderCollection->getItems() as $order)
      {
        $orderModel = Mage::getModel('sales/order');
        $orderModel->load($order['entity_id']);

        if(!$orderModel->canCancel())
            continue;

        $orderModel->cancel();
        $orderModel->setStatus($selectedStatusOut);
        $orderModel->save();
      }
    }

	}

}
