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

    $orderCollection = Mage::getResourceModel('sales/order_collection');

    $orderCollection //estado = pagamento pendente
      ->addFieldToFilter('state', 'processing')//pedidos com o estado de pagamento pendente
      ->addFieldToFilter('created_at', array(
      'lt' =>  new Zend_Db_Expr("DATE_ADD('".now()."', INTERVAL -'7' DAY)"))) //pega os pedidos criados a T atrás
      ->getSelect()->order('entity_id')->limit(10) //limite de itens por job
    ;

    $orders=""; //inicializa pedidos

    foreach($orderCollection->getItems() as $order){
      $orderModel = Mage::getModel('sales/order');
      $orderModel->load($order['entity_id']);

      if(!$orderModel->canCancel()) //verifica se é possível cancelar do estado atual
                        continue;

      $orderModel->cancel(); //executa o processo de cancelamento do pedido
      $orderModel->setStatus('canceled'); //seta o status para cancelado
      $orderModel->save(); //salva as alterações
    }
  }
}
