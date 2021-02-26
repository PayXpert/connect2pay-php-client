<?php

namespace PayXpert\Connect2Pay\containers\constant;

class SubscriptionCancelReason
{
    /**
     * Bank denial
     */
    const BANK_DENIAL = 1000;
    /**
     * Canceled due to refund
     */
    const REFUNDED = 1001;
    /**
     * Canceled due to retrieval request
     */
    const RETRIEVAL = 1002;
    /**
     * Cancellation letter sent by bank
     */
    const BANK_LETTER = 1003;
    /**
     * Chargeback
     */
    const CHARGEBACK = 1004;
    /**
     * Company account closed
     */
    const COMPANY_ACCOUNT_CLOSED = 1005;
    /**
     * Site account closed
     */
    const WEBSITE_ACCOUNT_CLOSED = 1006;
    /**
     * Didn't like the site
     */
    const DID_NOT_LIKE = 1007;
    /**
     * Disagree ('Did not do it' or 'Do not recognize the transaction')
     */
    const DISAGREE = 1008;
    /**
     * Fraud from webmaster
     */
    const WEBMASTER_FRAUD = 1009;
    /**
     * I could not get in to the site
     */
    const COULD_NOT_GET_INTO = 1010;
    /**
     * No problem, just moving on
     */
    const NO_PROBLEM = 1011;
    /**
     * Not enough updates
     */
    const NOT_UPDATED = 1012;
    /**
     * Problems with the movies/videos
     */
    const TECH_PROBLEM = 1013;
    /**
     * Site was too slow
     */
    const TOO_SLOW = 1014;
    /**
     * The site did not work
     */
    const DID_NOT_WORK = 1015;
    /**
     * Too expensive
     */
    const TOO_EXPENSIVE = 1016;
    /**
     * Un-authorized signup by family member
     */
    const UNAUTH_FAMILLY = 1017;
    /**
     * Undetermined reasons
     */
    const UNDETERMINED = 1018;
    /**
     * Webmaster requested to cancel
     */
    const WEBMASTER_REQUESTED = 1019;
    /**
     * I haven't received my item
     */
    const NOTHING_RECEIVED = 1020;
    /**
     * The item was damaged or defective
     */
    const DAMAGED = 1021;
    /**
     * The box was empty
     */
    const EMPTY_BOX = 1022;
    /**
     * The order was incomplete
     */
    const INCOMPLETE_ORDER = 1023;
}