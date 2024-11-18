<?php

namespace App\Utilities;

class Constant
{
    const USER_CIVILITY_MR = 1;
    const USER_CIVILITY_MRS = 2;
    const USER_CIVILITY_MISS = 3;
    const USER_CIVILITY = [
        self::USER_CIVILITY_MR => 'Mr',
        self::USER_CIVILITY_MRS => 'Mme',
        self::USER_CIVILITY_MISS => 'Mlle',
    ];

    const USER_MARITAL_STATUS_MARRIED = 1;
    const USER_MARITAL_STATUS_SINGLE = 2;
    const USER_MARITAL_STATUS_WIDOWER = 3;
    const USER_MARITAL_STATUS_DEVORCEE = 4;
    const USER_MARITAL_STATUS = [
        self::USER_MARITAL_STATUS_MARRIED => 'Marié(e)',
        self::USER_MARITAL_STATUS_SINGLE => 'Célibataire',
        self::USER_MARITAL_STATUS_WIDOWER => 'Veuf(ve)',
        self::USER_MARITAL_STATUS_DEVORCEE => 'Divorcé(e)',
    ];

    const TYPE_TIME_AM = 1;
    const TYPE_TIME_PM = 2;
    const TYPE_TIME = [
        self::TYPE_TIME_AM => 'AM',
        self::TYPE_TIME_PM => 'PM',
    ];

    const USER_PAYMENT_METHOD_VIREMENT = 1;
    const USER_PAYMENT_METHOD_CHEQUE = 2;
    const USER_PAYMENT_METHOD = [
        self::USER_PAYMENT_METHOD_VIREMENT => 'Virement',
        self::USER_PAYMENT_METHOD_CHEQUE => 'Chèque',
    ];

    const USER_CHOICE_LIVRAISON_BANQUE = 1;
    const USER_CHOICE_LIVRAISON_PLACE = 2;
    const USER_CHOICE_LIVRAISON_TIERCE = 3;
    const USER_CHOICE_LIVRAISON = [
        self::USER_CHOICE_LIVRAISON_BANQUE => 'depotBanque',
        self::USER_CHOICE_LIVRAISON_PLACE => 'surPlace',
        self::USER_CHOICE_LIVRAISON_TIERCE => 'tierce',
    ];
    const USER_CHOICE_LIVRAISON_LABEL = [
        self::USER_CHOICE_LIVRAISON_BANQUE => 'Conservation auprès de BFM',
        self::USER_CHOICE_LIVRAISON_PLACE => 'Retrait auprès de BFM',
        self::USER_CHOICE_LIVRAISON_TIERCE => 'Retrait par un mandataire',
    ];

    const USER_CHOICE_MEETING_SIEGE = 1;
    const USER_CHOICE_MEETING_RT = 2;
    const USER_CHOICE_MEETING = [
        self::USER_CHOICE_MEETING_SIEGE => 'Siège Antaninarenina',
        self:: USER_CHOICE_MEETING_RT => 'RT',
    ];

    const USER_RT_ANTSIRANANA = 1;
    const USER_RT_SAMBAVA = 2;
    const USER_RT_MAHAJANGA = 3;
    const USER_RT_MORONDAVA = 4;
    const USER_RT_MANAKARA = 5;
    const USER_RT_TOLIARA = 6;
    const USER_RT_NOSYBE = 7;
    const USER_RT_TOAMASINA = 8;
    const USER_RT_ANTSIRABE = 9;
    const USER_RT_TOLAGNARO = 10;
    const USER_RT_FIANARANTSOA = 11;
    const USER_RT = [
        self::USER_RT_ANTSIRANANA => 'Antsiranana',
        self:: USER_RT_SAMBAVA => 'Sambava',
        self:: USER_RT_MAHAJANGA => 'Mahajanga',
        self:: USER_RT_MORONDAVA => 'Morondava',
        self:: USER_RT_MANAKARA => 'Manakara',
        self:: USER_RT_TOLIARA => 'Toliara',
        self:: USER_RT_NOSYBE => 'Nosy Be',
        self:: USER_RT_TOAMASINA => 'Toamasina',
        self:: USER_RT_ANTSIRABE => 'Antsirabe',
        self:: USER_RT_TOLAGNARO => 'Tolagnaro',
        self:: USER_RT_FIANARANTSOA => 'Fianarantsoa',
    ];

    const USER_ACCOUNT_BANK = 1;
    const USER_ACCOUNT_IMF = 2;
    const USER_ACCOUNT_FOREIGNERS = 3;
    const USER_ACCOUNT = [
        self::USER_ACCOUNT_BANK => 'Banque locale',
        self::USER_ACCOUNT_IMF => 'IMF',
        self::USER_ACCOUNT_FOREIGNERS => 'Banque extérieure',
    ];

    const ORDER_TYPE_PREORDER = 1;
    const ORDER_TYPE_WAITING_LIST = 2;
    const ORDER_TYPE = [
        self::ORDER_TYPE_PREORDER => 'Liste de précommande',
        self::ORDER_TYPE_WAITING_LIST => 'Liste d\'attente',
    ];

    const STATUS_WAIT = 0;
    const STATUS_VALID = 1;
    const STATUS_QUEUE = 2;
    const STATUS_PAYMENT_WAIT = 3;
    const STATUS_PAYMENT_WAIT_CHECK_ACCEPTED = 31;
    const STATUS_PAYMENT_WAIT_CHECK_REFUSED = 399;
    const STATUS_PAID = 4;
    const STATUS_APPOINTMENT = 5;
    const STATUS_DELIVERED = 6;
    const STATUS_CANCEL = 99;
    const STATUS_TYPE = [
        self::STATUS_WAIT => 'En attente de validation',
        self::STATUS_QUEUE => 'Sur la liste d\'attente',
        self::STATUS_VALID => 'En cours de traitement',
        self::STATUS_PAYMENT_WAIT => 'En attente de paiement',
        self::STATUS_PAYMENT_WAIT_CHECK_ACCEPTED => 'En attente de paiement - Chèque accepté',
        self::STATUS_PAYMENT_WAIT_CHECK_REFUSED => 'Chèque refusé',
        self::STATUS_PAID => 'Paiement accepté',
        self::STATUS_APPOINTMENT => 'Rendez-vous pris',
        self::STATUS_DELIVERED => 'Pièce livrée',
        self::STATUS_CANCEL => 'Annulée',
    ];

    const FILTER_AGE = 30;

    const CHECKED_NATIONALITE = 1;
    const NOCHECKED_NATIONALITE = 0;
    const NAME_CHECKBOX_NATIONALITE = "NATIONALITY";
    const NAME_CAMPAIGN_END_DATE = "CAMPAIGN_END_DATE";
    const NAME_CGV = "CGV";
    const NAME_FAQ = "FAQ";
}
