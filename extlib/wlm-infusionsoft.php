<?php
if ( ! class_exists('WLM_Infusionsoft_Connection') ) {
    include_once( $this->pluginDir . '/extlib/wlm-infusionsoft-connection.php' );
}

if ( !class_exists( 'WLM_Infusionsoft' ) ) {

    class WLM_Infusionsoft {

        private $ifsdk_con    = NULL;
        private $ifservices   = NULL;

        function __construct( $machine_name, $api_key, $apilogfile = false ) {
            $this->ifsdk_con = new WLM_Infusionsoft_Connection;

            if ( $apilogfile ) {
                $this->ifsdk_con->enable_logging( $apilogfile );
            }

            $this->ifsdk_con->set_connection( $machine_name, $api_key );
        }

        function is_api_connected(){
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            return true;
        }

        function create_contact( $cdetails, $optin_reason = "Contact Opted In through the WLM API" ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            return $this->ifservices->addCon( $cdetails, $optin_reason );
        }

        function get_contact_details( $contactid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array('Id', 'FirstName', 'LastName', 'Email', 'Company', 'StreetAddress1', 'StreetAddress2', 'City', 'State', 'PostalCode', 'Country');
            $contact = $this->ifservices->loadCon( $contactid, $fields );
            return $contact;
        }

        function get_contactid_by_email( $email ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            $c = $this->ifservices->findByEmail( $email, array("Id") );

            if ( $c ) {
                return $c[0]["Id"];
            }

            return  false;
        }

        function assign_followup_sequence( $cid, $campid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            return $this->ifservices->campAssign( $cid, $campid );
        }

        function remove_followup_sequence( $cid, $campid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            return $this->ifservices->campRemove( $cid, $campid );
        }

        function get_contactid_by_invoice( $invid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            $invoice = $this->ifservices->dsFind( 'Invoice', 1, 0, 'Id', $invid, array( 'ContactId') );

            if ( $invoice )
                return isset($invoice[0]['ContactId']) ? $invoice[0]['ContactId']:false;
            else
                return false;
        }

        function optin_contact_email( $email, $reason = 'Contact Opted In through the WLM API' ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            return $this->ifservices->optIn( $email, $reason );
        }

        function get_tag_categories() {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            $page = 0;
            $tags_category = array();
            while ( $page >= 0 ) { //lets fetch records by 1000 per call/page
                $tcategory = $this->ifservices->dsQuery( 'ContactGroupCategory', 1000, $page, array('Id'=>'%'), array( 'Id', 'CategoryName', 'CategoryDescription' ) );
                if ( $tcategory ) {
                    foreach ( $tcategory as $id => $data ) {
                        $tags_category[ $data["Id"] ] = $data["CategoryName"];
                    }
                    $page++;
                } else {
                    $page = -1;
                }
            }
            return $tags_category;
        }

        function get_tags() {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            $page = 0;
            $tags = array();
            while ( $page >= 0 ) { //lets fetch records by 1000 per call/page
                $t = $this->ifservices->dsQuery( 'ContactGroup', 1000, $page, array('Id'=>'%'), array( 'Id', 'GroupName', 'GroupCategoryId' ) );
                if ( $t ) {
                    foreach ( $t as $id => $data ){
                        $tags[ $data["GroupCategoryId"] ][] = array(
                            "Id" => $data["Id"],
                            "Name" => $data["GroupName"],
                        );
                    }
                    $page++;
                } else {
                    $page = -1;
                }
            }
            asort( $tags );
            return $tags;
        }

        function tag_contact( $contactid, $tagid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            $ret = $this->ifservices->grpAssign( $contactid, $tagid );
            if ( $ret ) {
                return $ret;
            } else {
                $t = array("errno"=>1,"errstr"=>"Apply Tag Error");
            }
        }

        function untag_contact( $contactid, $tagid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return null;
            $ret = $this->ifservices->grpRemove( $contactid, $tagid );
            if ( $ret ) {
                return $ret;
            } else {
                $t = array("errno"=>1,"errstr"=>"Remove Tag Error");
            }
        }

        function get_orderid_job( $orderid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array('Id', 'JobTitle', 'ContactId', 'StartDate', 'DueDate', 'JobNotes', 'ProductId', 'JobStatus', 'DateCreated', 'JobRecurringId', 'OrderType', 'OrderStatus' );
            $res = $this->ifservices->dsFind( 'Job', 1, 0, 'Id', $orderid, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

        function get_invoice_details( $invoiceid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'ContactId', 'JobId', 'DateCreated', 'TotalDue', 'PayStatus', 'CreditStatus', 'RefundStatus', 'PayPlanStatus', 'InvoiceType', 'ProductSold' );
            $res = $this->ifservices->dsFind( 'Invoice', 1, 0, 'Id', $invoiceid, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

        function get_jobid_invoice( $jobid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'ContactId', 'DateCreated', 'TotalDue', 'JobId', 'PayStatus', 'CreditStatus', 'RefundStatus', 'PayPlanStatus', 'InvoiceType', 'ProductSold' );
            $res = $this->ifservices->dsFind( 'Invoice', 1, 0, 'JobId', $jobid, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

        function get_cidpid_recurringorder( $contactid, $pid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'ContactId', 'OriginatingOrderId', 'SubscriptionPlanId', 'ProductId', 'StartDate', 'EndDate', 'LastBillDate', 'NextBillDate', 'ReasonStopped', 'Status' );
            $query  = array( 'ContactId'=>$contactid, 'ProductId'=>$pid );
            $res = $this->ifservices->dsQuery( 'RecurringOrder', 1, 0, $query, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

        function get_subscriptionid_recurringorder( $subscriptionid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'ContactId', 'OriginatingOrderId', 'SubscriptionPlanId', 'ProductId', 'StartDate', 'EndDate', 'LastBillDate', 'NextBillDate', 'ReasonStopped', 'Status' );
            $query  = array( 'Id'=>$subscriptionid );
            $res = $this->ifservices->dsQuery( 'RecurringOrder', 1, 0, $query, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

        function get_cidjobid_recurringorder( $contactid, $jobid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'ContactId', 'OriginatingOrderId', 'SubscriptionPlanId', 'ProductId', 'StartDate', 'EndDate', 'LastBillDate', 'NextBillDate', 'ReasonStopped', 'Status' );
            $query  = array( 'ContactId'=>$contactid, 'OriginatingOrderId'=>$jobid );
            $res = $this->ifservices->dsQuery( 'RecurringOrder', 1, 0, $query, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

        function get_invoice_payments( $invoiceid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'InvoiceId', 'Amt', 'PayStatus', 'PaymentId', 'SkipCommission' );
            $query  = array( 'InvoiceId'=>$invoiceid );
            $page = 0;
            $payments = array();
            while ( $page >= 0 ) { //lets fetch records by 1000 per call/page
                $res = $this->ifservices->dsQuery( 'InvoicePayment', 1000, $page, $query, $fields );
                if ( $res ) {
                    foreach ( $res as $id => $data ){
                        $payments[ $id ] = $data;
                    }
                    $page++;
                } else {
                    $page = -1;
                }
            }

            if ( $payments )
                return $payments;
            else
                return false;
        }

        function get_subscriptionid_jobs( $subscriptionid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'JobTitle', 'ContactId', 'JobRecurringId', 'ProductId' );
            $query  = array( 'JobRecurringId'=>$subscriptionid );
            $page = 0;
            $jobs = array();
            while ( $page >= 0 ) { //lets fetch records by 1000 per call/page
                $res = $this->ifservices->dsQuery( 'Job', 1000, $page, $query, $fields );
                if ( $res ) {
                    foreach ( $res as $id => $data ){
                        $jobs[ $id ] = $data;
                    }
                    $page++;
                } else {
                    $page = -1;
                }
            }

            if ( $jobs )
                return $jobs;
            else
                return false;
        }

        function get_invoice_payplan( $invoiceid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'InvoiceId', 'AmtDue', 'DateDue', 'InitDate', 'StartDate' );
            $res = $this->ifservices->dsFind( 'PayPlan', 1, 0, 'InvoiceId', $invoiceid, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

        function get_payplan_items( $payplanid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id', 'PayPlanId', 'DateDue', 'AmtDue', 'Status' );
            $query  = array( 'PayPlanId'=> $payplanid );
            $page = 0;
            $ppi = array();
            while ( $page >= 0 ) { //lets fetch records by 1000 per call/page
                $res = $this->ifservices->dsQuery( 'PayPlanItem', 1000, $page, $query, $fields );
                if ( $res ) {
                    foreach ( $res as $id => $data ){
                        $ppi[ $id ] = $data;
                    }
                    $page++;
                } else {
                    $page = -1;
                }
            }

            if ( $ppi )
                return $ppi;
            else
                return false;
        }

        function get_product_sku( $pid ) {
            $this->ifservices = $this->ifsdk_con->get_connection();
            if ( ! $this->ifservices ) return false;
            $fields = array( 'Id','Sku','ProductName' );
            $res = $this->ifservices->dsFind( 'Product', 1, 0, 'Id', $pid, $fields );
            if ( $res )
                return $res[0];
            else
                return false;
        }

    }
}
?>