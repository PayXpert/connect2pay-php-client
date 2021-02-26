<?php

namespace PayXpert\Connect2Pay\containers;

use PayXpert\Connect2Pay\helpers\C2PValidate;
use PayXpert\Connect2Pay\helpers\Utils;
use PayXpert\Connect2Pay\containers\constant\OrderType;
use PayXpert\Connect2Pay\containers\constant\OrderShippingType;
use PayXpert\Connect2Pay\containers\constant\OrderDeliveryDelay;

class Order extends Container
{
    private $id;

    private $type;

    private $shippingType;

    private $deliveryDelay;

    private $deliveryEmail;

    private $reorder;

    private $preOrder;

    private $preOrderDate;

    private $prepaidAmount;

    private $prepaidCurrency;

    private $prepaidCount;

    private $shopperLoyaltyProgram;

    private $totalWithoutShipping;

    private $shippingPrice;

    private $discount;

    private $description;

    private $cartContent;

    private $affiliateID;

    private $campaignName;

    /**
     * @var Recurrence
     */
    private $recurrence;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Merchant internal unique order ID
     *
     * @param string $id
     * @return Order
     */
    public function setId($id)
    {
        $this->id = $this->limitLength($id, 100);
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Order
     */
    public function setType($type)
    {
        $validValues = [
            OrderType::GOODS_SERVICE,
            OrderType::CHECK_ACCEPTANCE,
            OrderType::ACCOUNT_FUNDING,
            OrderType::QUASI_CASH,
            OrderType::PREPAID_LOAN
        ];

        if (in_array($type, $validValues)) {
            $this->type = $this->limitLength($type, 2);
        } else {
            Utils::error("Bad value for order.type: " . $type);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getShippingType()
    {
        return $this->shippingType;
    }

    /**
     * @param string $shippingType
     * @return Order
     */
    public function setShippingType($shippingType)
    {
        $validValues = [
            OrderShippingType::TO_CARDHOLDER,
            OrderShippingType::TO_VERIFIED_ADDRESS,
            OrderShippingType::TO_NON_BILLING_ADDRESS,
            OrderShippingType::SHIP_TO_STORE,
            OrderShippingType::DIGITAL_GOODS,
            OrderShippingType::TRAVEL_EVENT_TICKET,
            OrderShippingType::OTHER
        ];

        if (in_array($shippingType, $validValues)) {
            $this->shippingType = $this->limitLength($shippingType, 2);
        } else {
            Utils::error("Bad value for order.shippingType: " . $shippingType);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryDelay()
    {
        return $this->deliveryDelay;
    }

    /**
     * @param string $deliveryDelay
     * @return Order
     */
    public function setDeliveryDelay($deliveryDelay)
    {
        $validValues = [
            OrderDeliveryDelay::ELECTRONIC,
            OrderDeliveryDelay::SAME_DAY,
            OrderDeliveryDelay::OVERNIGHT,
            OrderDeliveryDelay::TWO_OR_MORE_DAY,
        ];

        if (in_array($deliveryDelay, $validValues)) {
            $this->deliveryDelay = $this->limitLength($deliveryDelay, 2);
        } else {
            Utils::error("Bad value for order.deliveryDelay: " . $deliveryDelay);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryEmail()
    {
        return $this->deliveryEmail;
    }

    /**
     * @param string $deliveryEmail
     * @return Order
     */
    public function setDeliveryEmail($deliveryEmail)
    {
        if (C2PValidate::isEmail($deliveryEmail) || $deliveryEmail == 'NA') {
            $this->deliveryEmail = $this->limitLength($deliveryEmail, 100);
        } else {
            Utils::error("Invalid order.deliveryEmail provided: " . $deliveryEmail);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getReorder()
    {
        return $this->reorder;
    }

    /**
     * @param bool $reorder
     * @return Order
     */
    public function setReorder($reorder)
    {
        $this->reorder = $reorder;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPreOrder()
    {
        return $this->preOrder;
    }

    /**
     * @param bool $preOrder
     * @return Order
     */
    public function setPreOrder($preOrder)
    {
        $this->preOrder = $preOrder;
        return $this;
    }

    /**
     * @return string
     */
    public function getPreOrderDate()
    {
        return $this->preOrderDate;
    }

    /**
     * @param string $preOrderDate
     * @return Order
     */
    public function setPreOrderDate($preOrderDate)
    {
        $this->preOrderDate = $this->limitLength($preOrderDate, 8);
        return $this;
    }

    /**
     * @return int
     */
    public function getPrepaidAmount()
    {
        return $this->prepaidAmount;
    }

    /**
     * @param int $prepaidAmount
     * @return Order
     */
    public function setPrepaidAmount($prepaidAmount)
    {
        $this->prepaidAmount = $prepaidAmount;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrepaidCurrency()
    {
        return $this->prepaidCurrency;
    }

    /**
     * @param string $prepaidCurrency
     * @return Order
     */
    public function setPrepaidCurrency($prepaidCurrency)
    {
        $this->prepaidCurrency = $this->limitLength($prepaidCurrency, 3);
        return $this;
    }

    /**
     * @return int
     */
    public function getPrepaidCount()
    {
        return $this->prepaidCount;
    }

    /**
     * @param int $prepaidCount
     * @return Order
     */
    public function setPrepaidCount($prepaidCount)
    {
        $this->prepaidCount = $prepaidCount;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopperLoyaltyProgram()
    {
        return $this->shopperLoyaltyProgram;
    }

    /**
     * @param string $shopperLoyaltyProgram
     * @return Order
     */
    public function setShopperLoyaltyProgram($shopperLoyaltyProgram)
    {
        $this->shopperLoyaltyProgram = $this->limitLength($shopperLoyaltyProgram, 50);
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalWithoutShipping()
    {
        return $this->totalWithoutShipping;
    }

    /**
     * The transaction amount in cents, without shipping fee
     *
     * @param int $totalWithoutShipping
     * @return Order
     */
    public function setTotalWithoutShipping($totalWithoutShipping)
    {
        if (C2PValidate::isInt($totalWithoutShipping)) {
            $this->totalWithoutShipping = (int) $totalWithoutShipping;
        } else {
            Utils::error("Bad value for totalWithoutShipping: " . $totalWithoutShipping);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getShippingPrice()
    {
        return $this->shippingPrice;
    }

    /**
     * The shipping amount in cents (for 1€ => 100)
     *
     * @param int $shippingPrice
     * @return Order
     */
    public function setShippingPrice($shippingPrice)
    {
        if (C2PValidate::isInt($shippingPrice)) {
            $this->shippingPrice = (int) $shippingPrice;
        } else {
            Utils::error("Bad value for shippingPrice: " . $shippingPrice);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * The discount amount in cents (for 1€ => 100)
     *
     * @param int $discount
     * @return Order
     */
    public function setDiscount($discount)
    {
        if (C2PValidate::isInt($discount)) {
            $this->discount = (int) $discount;
        } else {
            Utils::error("Bad value for discount: " . $discount);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sum up of the order to display on the payment page
     *
     * @param string $description
     * @return Order
     */
    public function setDescription($description)
    {
        $this->description = $this->limitLength($description, 500);
        return $this;
    }

    /**
     * @return array
     */
    public function getCartContent()
    {
        return $this->cartContent;
    }

    /**
     * Product or service bought - see details below
     *
     * @param array[](integer CartProductId, string CartProductName, float
     *      CartProductUnitPrice,
     *      integer CartProductQuantity, string CartProductBrand, string
     *      CartProductMPN,
     *      string CartProductCategoryName, integer CartProductCategoryID) $cartContent
     * @return Order
     */
    public function setCartContent($cartContent)
    {
        $this->cartContent = $cartContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getAffiliateID()
    {
        return $this->affiliateID;
    }

    /**
     * @param string $affiliateID
     * @return Order
     */
    public function setAffiliateID($affiliateID)
    {
        if (C2PValidate::isNumeric($affiliateID)) {
            $this->affiliateID = $this->limitLength($affiliateID, 16);
        } else {
            Utils::error("Bad value for order.affiliateID: " . $affiliateID);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCampaignName()
    {
        return $this->campaignName;
    }

    /**
     * @param string $campaignName
     * @return Order
     */
    public function setCampaignName($campaignName)
    {
        $this->campaignName = $this->limitLength($campaignName, 128);
        return $this;
    }

    /**
     * @return Recurrence
     */
    public function getRecurrence()
    {
        return $this->recurrence;
    }

    /**
     * @param Recurrence $recurrence
     * @return Order
     */
    public function setRecurrence($recurrence)
    {
        $this->recurrence = $recurrence;
        return $this;
    }

    public function addCartProduct($product)
    {
        if ($this->cartContent == null) {
            $this->cartContent == [];
        }
        $this->cartContent[] = $product;
    }
}