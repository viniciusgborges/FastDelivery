<?php

declare(strict_types=1);

namespace Vb\FastDelivery\Plugin;

use \Psr\Log\LoggerInterface;
use Vb\FastDelivery\Model\Config;
use Vb\FastDelivery\Helper\Email;
use \Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as StatusCollectionFactory;

class OrderStatePlugin
{
    protected $logger;
    protected $config;
    protected $helper;
    protected $orderStatusCollection;
    protected $statusCollectionFactory;
    protected $shippingMethod = 'fastdelivery_fastdelivery';

    public function __construct(
        LoggerInterface $logger,
        \Vb\FastDelivery\Model\Config $config,
        \Vb\FastDelivery\Helper\Email $helper,
        OrderStatusCollection $orderStatusCollection,
        StatusCollectionFactory $statusCollectionFactory
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->helper = $helper;
        $this->orderStatusCollection = $orderStatusCollection;
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    public function afterSave(
        \Magento\Sales\Model\ResourceModel\Order $subject,
         $result,$object
    ){
        $shippingMethod = $object->getShippingMethod();

        if ($shippingMethod == $this->shippingMethod) {

            $incrementId = $object->getIncrementId();

            $status = $object->getData('status');
            $statusCollectionFac = $this->statusCollectionFactory->create()->toOptionArray();
            $statusToEmail = $this->config->getOrderStatus();
            $statusToEmailArray = explode(',', $statusToEmail);

            foreach ($statusCollectionFac as $key => $statusField) {
                if ($statusField['value'] == $status) {
                    $statusTranslated = $statusField['label'];
                }
            }

            $storeSenderName = $this->config->getStoreName();
            $storeSenderEmail = $this->config->getStoreSenderEmail();

            if (in_array($status, $statusToEmailArray)) {
                $this->helper->sendEmail($incrementId, $statusTranslated, $storeSenderName, $storeSenderEmail);
            }
        }
    }
}
