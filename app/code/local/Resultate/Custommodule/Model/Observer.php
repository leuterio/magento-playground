<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/SmashingMagazine/
 *                     LogProductUpdate/Model/Observer.php
 */
class Resultate_Custommodule_Model_Observer{


  public function cancelUnpaidOrders(){



    $selectedStatusUnformated = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_status_in');
    $selectedStatusIn = "'".$selectedStatusUnformated."'";
    $selectedStatusOut = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_status_out');
    $selectedDays = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_day');
    $selectedTimeUnformated = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_time');
    $selectedTime = str_replace(',', ':', $selectedTimeUnformated); //formatando a hora para hh:mm:ss
    $selectedIsEnabled = Mage::getStoreConfig('expiry_options/expiry_selection/expiry_enable');

    Mage::log('Status entrada: '.$selectedStatusIn);
    Mage::log('Status saída: '.$selectedStatusOut);
    Mage::log('Dias: '.$selectedDays);
    Mage::log('Horas: '.$selectedTime);
    Mage::log('Habilitado: '.$selectedIsEnabled);

    if($selectedIsEnabled == '1'){
      $orderCollection //estado = pagamento pendente
        ->addFieldToFilter('status', $selectedStatusIn)//pedidos com o estado de pagamento pendente
        ->addFieldToFilter('created_at', array(
        'lt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'$selectedDays $selectedTime'  DAY_SECOND)"))) //pega os pedidos criados a T atrás
        ->getSelect()->order('entity_id')->limit(10) //limite de itens por job
      ;

      $orders=""; //inicializa pedidos

      foreach($orderCollection->getItems() as $order){
        $orderModel = Mage::getModel('sales/order');
        $orderModel->load($order['entity_id']);

        if(!$orderModel->canCancel()) //verifica se é possível cancelar do estado atual
                          continue;

        Mage::log('pode ser cancelado');
        
        $orderModel->cancel(); //executa o processo de cancelamento do pedido
        $orderModel->setStatus($selectedStatusOut); //seta o novo status
        $orderModel->save(); //salva as alterações
      }
    }

  }
}
