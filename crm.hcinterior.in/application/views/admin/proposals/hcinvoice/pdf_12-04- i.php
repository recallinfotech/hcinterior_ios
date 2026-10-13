 <style>
/* PDF styles only */
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #000;
}

h1,
h2,
h3 {
    margin: 0 0 10px 0;
    font-weight: bold;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
}

th,
td {
    border: 1px solid #787878ff;
    padding: 4px;
    text-align: left;
    font-size: 10px;
}

th {
    background: #f0f0f0;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}


.container {

    background: #fff;
}

.header {
    width: 100%;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
}

.header-table td {
    vertical-align: top;
}

.logo {
    width: 120px;
}

.social-section {
    border-left: 2px solid #444;
    padding-left: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-family: Arial, sans-serif;
}

.social-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.social-item img {
    width: 10px;
    height: 10px;
}

.contact-section {
    border-left: 2px solid #444;
    padding-left: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-family: Arial, sans-serif;
    font-size: 14px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.4;
    font-size: 12px
}

.contact-item img {
    width: 10px;
    height: 10px;
   // margin-top: 2px;
}

.gst-bar {
    background-color: #000;
    color: #fff;
    padding: 5px 10px;
    font-weight: bold;
    font-size: 13px;
}

/* Quotation Section Fixed */
.quote-box {
    display: flex;
    justify-content: space-between;
    border-left: 1px solid #000;
    border-right: 1px solid #000;
    border-bottom: 1px solid #000;
    padding: 15px;
}

.quote-left {
    width: 60%;
}

.quote-right {
    width: 38%;
    text-align: right;
}

.quote-left p,
.quote-right p {
    margin: 6px 0;
}

.sub-footer {
    background-color: #95b3d7;
    color: #000;
    font-weight: bold;
    text-align: center;
    padding: 5px;
    font-size: 13px;
    border-left: 1px solid #000;
    border-right: 1px solid #000;
    border-bottom: 1px solid #000;
}

.section-title {
    background-color: #cde0f4;
    font-weight: bold;
    padding: 6px;
    border: 1px solid #000;
    /* margin-top: 10px; */
    text-align: center;
    font-size: 16px;
}

table {
    width: 100%;
    border-collapse: collapse;
    /* margin-top: 10px; */
}

table,
th,
td {
    border: 1px solid #000;
}

th,
td {
    padding: 5px;
    vertical-align: top;
}

.product-img {
    width: 100px;
    height: auto;
}

.remarks {
    font-style: italic;
    margin-top: 5px;
}

h5 {
    margin: 0;
    padding: 0;
    margin-bottom: 5px !important;
}

/* Blue section row (like "ENTRANCE") */
.section-row td {
    background-color: #95b3d7;
    color: #000;
    font-weight: bold;

}
 </style>




 <div class="container">

     <!-- Header Section -->
     <div class="header">
         <table class="header-table">
             <tr>

                 <td width="30%">
                     <img src="<?php echo base_url();?>asset/images/hcinvoice/hc.jpg" alt="Logo" class="logo" />
                 </td>
                 <td width="30%">
                     <div class="social-section">
                         <div class="social-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconsinstagram.png"
                                 alt="Instagram" />
                             <span>@highcreation</span>
                         </div>
                         <div class="social-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconsyoutube.png" alt="YouTube" />
                             <span>@hcinterior</span>
                         </div>
                         <div class="social-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconslinkedin.png"
                                 alt="LinkedIn" />
                             <span>@highcreationinterior</span>
                         </div>
                         <div class="social-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconsfacebook.png"
                                 alt="Facebook" />
                             <span>@hcinterior</span>
                         </div>
                     </div>
                 </td>
                 <td width="40%">
                     <div class="contact-section">
                         <div class="contact-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconsphone.png" alt="Phone" />
                             <span>18001200532</span>
                         </div>
                         <div class="contact-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconsemail.png" alt="Email" />
                             <span>info@hcinterior.in</span>
                         </div>
                         <div class="contact-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconslocation.png"
                                 alt="Noida Office" />
                             <span>H-101, LGF, H Block, Sector 63, Noida<br>U.P. 201301402</span>
                         </div>
                         <div class="contact-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconslocation.png"
                                 alt="Gurugram Office" />
                             <span>Office No. 402 & 402A, 4th Floor, JMD Galleria Mall, Badshahpur Sohna
                                 Road,<br>Sector-48,
                                 Gurugram 122001</span>
                         </div>
                         <div class="contact-item">
                             <img src="<?php echo base_url();?>asset/images/hcinvoice/iconsglobe.png" alt="Website" />
                             <span>www.hcinterior.in</span>
                         </div>
                     </div>
                 </td>
             </tr>
         </table>
     </div>

     <!-- GST Bar -->
     <div class="gst-bar">GSTIN : 09AAECH5510R1Z5</div>

     <!-- Quotation Box -->
     <div class="quote-box">

         <table>
             <tr>
                 <td>
                     <div class="quote-left">
                         <p><strong>Quotation For</strong></p>
                         <p>Mr./ Mrs. <?php 
 

if (!empty($proposals_info->phone)) {
    $this->db->select('*');
    $this->db->from('tbl_leads');
    $this->db->where('phone', $proposals_info->phone);
    $lead_info = $this->db->get()->row(); // returns single object

    if (!empty($lead_info)) {
        echo $lead_info->contact_name;
   

?>




                         </p>
                         <p><?php echo $lead_info->address; ?>, <?php echo $lead_info->city; ?></p>
                         <p>Contact Person- <?php echo $lead_info->contact_name; ?></p>
                         <p>Subject- Offer for Home Interior Work</p>
                         <p>Dear Sir/ Ma'am,</p>
                         <p>Greeting of the day!!</p>
                         <p>This is with reference of your requirement.</p>
                         <?php  } } ?>
                     </div>
                 </td>
                 <td>
                     <div class="">
                         <p><strong>Quote No-</strong> <?php echo $proposals_info->ref_no;?></p>
                         <p><strong>Date-</strong><?php
if (!empty($proposals_info->proposal_date)) {
    echo date("d-m-Y", strtotime($proposals_info->proposal_date)); 
}
?></p>
                         <p><strong>Quotation Valid till</strong> 3 months</p>
                         <p><strong>Prepared By :</strong> Mr. / Mrs. <?php 
 
 
if (!empty($proposals_info->created_by)) {
    $this->db->select('*');
    $this->db->from('tbl_account_details');
    $this->db->where('user_id', $proposals_info->created_by);
    $user_info = $this->db->get()->row(); // returns single object

    if (!empty($user_info)) {
        echo $user_info->fullname;
    }
  }
?>
                         </p>
                         <p><strong>Checked By : </strong> Mr. / Mrs. <?php echo $proposals_info->checked_by;?></p>
                     </div>
                 </td>
             </tr>
         </table>


     </div>

     <!-- Footer Bar -->
     <div class="sub-footer">HIGH CREATION INTERIOR PROJECTS PVT.LTD.</div>

     <!-- Section Title -->

     <div id="floors-container2">

         <?php $i = 1;
                          foreach($proposal_floors as $floor_view) { ?>
         <div class="floor-section" id="floor_<?= $i;?>">
             <h4 class="head">
                 <b>🏢 <?php echo $floor_view['floor_name'];?></b>
             </h4>

             <div id="searchResults_<?= $i;?>"></div>
             <table class="table invoice-items-table items" id="itemTable_<?= $i;?>">
                 <thead style="background: #e8e8e8">
                     <tr>
                         <th style="text-align:center" width="20px">S.N.</th>
                         <th >Item Name</th>
                         <th >Description</th>
                          <th style="text-align:right" width="50px">Qty</th>
                         <th style="text-align:right" width="30px">Unit</th>
                        
                         <th style="text-align:right" width="80px">Price</th>
                         <th style="text-align:right" width="80px">Total</th>
                         <th style="text-align:right" width="80px">Remarks</th>


                     </tr>
                 </thead>
                 <tbody class="ui-sortable">
    <?php 
    foreach($proposal_floor_categories as $category_view){ 
        if($category_view['floor_id'] == $floor_view['id']) { 
    ?>

        <!-- Category Row -->
        <tr class="category-row section-title table-secondary"
            data-category="<?php echo  $category_view['category_name'];?>">
            <td colspan="8">
                <div style="text-align:center"><strong>
                    <?php echo $category_view['category_name'];?>
                </strong></div>
            </td>
        </tr>

        <?php 
        $last_location = ''; 
        $location_serial = 'A';  // Location numbering A, B, C
        $item_serial = 1;        // Item numbering reset for each location

        foreach($proposal_items as $item_view) { 
            if($item_view['category_id'] == $category_view['id']){  

                // Check if location has changed
                if($last_location != $item_view['location_name']){
                    $last_location = $item_view['location_name'];
                    $item_serial = 1; // Reset items for new location
                ?>
                    
                    <!-- Location Row -->
                    <tr class="section-row location-row table-info"
                        data-category="<?php echo $category_view['category_name'];?>"
                        data-location="<?php echo $item_view['location_name'];?>"
                        data-location_name_id="<?php echo $item_view['location_name_id'];?>">
                        <td style="text-align:center">
                            <strong><?php echo $location_serial; ?></strong>
                        </td>
                        <td colspan="7" style="text-align:center">
                            <strong><?php echo $item_view['location_name']; ?></strong>
                        </td>
                    </tr>
                <?php 
                    $location_serial++; // Next location letter
                } ?>

                <!-- Item Row -->
                <tr data-id="<?php echo $item_view['item_id'];?>" data-floor="<?= $i;?>"
                    data-category="<?php echo $category_view['category_name'];?>"
                    data-category_name_id="<?php echo $category_view['id'];?>"
                    data-location="<?php echo $item_view['location_name'];?>"
                    data-location_name_id="<?php echo $item_view['location_name_id'];?>">

                    <td style="text-align:center"><?php echo $item_serial;?></td>
                    <td>
                        <h5 style=""><?php echo $item_view['item_name']; ?></h5>
                        <?php if($item_view['image_url']) { ?>
                        <img src="<?= $item_view['image_url'] ?>" alt=" " width="100%">
                        <?php } ?>
                    </td>
                    <td>
                        <?php echo nl2br($item_view['description']); ?>
                        
                    </td>
                    <td style="text-align:right"><?php echo number_format($item_view['qty'], 0); ?></td>
                    <td style="text-align:right">
                        <?php   
                        echo $item_view['unit_type'];
                       /* $cname = $item_view['item_id'];
                        $query = $this->db
                            ->where('saved_items_id', $cname)
                            ->get('tbl_saved_items')
                            ->row();
                        if ($query) {
                            echo  $query->unit_type;
                        }  */
						?>
                    </td>
                    <td style="text-align:right"><?php echo number_format($item_view['price']); ?></td>
                    <td style="text-align:right"><?php echo number_format($item_view['total']); ?></td>
                     <td ><!-- Addons -->
                     <ul  style="margin:0; padding:5">
                        <?php foreach($proposal_item_addons as $item_addon) { 
                            if($item_addon['main_item_id'] == $item_view['item_id']  && $i == $item_addon['floor_id'] ) { ?>
                                <li style="font-size:8px; line-height:10px; margin:0; padding:0; margin-bottom:5px">
                                    <?php
                                    if (!empty($item_addon['main_item_id'])) {
                                        $item_info = $this->db->where('saved_items_id', $item_addon['addon_id'])
                                            ->get('tbl_saved_items')
                                            ->row();

                                        if (!empty($item_info)) {
                                            $product_cat = $this->db->where('product_category_id', $item_info->product_category_id)
                                                ->get('tbl_product_category')
                                                ->row();
                                            $product_sub_cat = $this->db->where('product_category_id', $item_info->product_sub_category_id)
                                                ->get('tbl_product_category')
                                                ->row();
                                            if (!empty($product_cat)) echo $product_cat->category_name;
                                            if (!empty($product_sub_cat)) echo ":- <br>" . $product_sub_cat->category_name;
                                            echo " " . "(". $item_addon['addon_name']. ")";
                                        }
                                    }
                                    ?>
                                </li>
                        <?php }} ?>
                                </ul>
                    </td>
                </tr>
        <?php 
                $item_serial++; // Increment item inside location
            } 
        } 
        ?>

        <!-- Subtotal / Discount / Total -->
        <tr class="category-subtotal-row table-light"
            data-category="<?php echo  $category_view['category_name'];?>">
            <td colspan="6" class="text-right"><strong>Sub Total:</strong></td>
            <td style="text-align:right"><?php echo  number_format($category_view['subtotal']);?></td>
            <td></td>
        </tr>
           <?php if($category_view['discount']) { ?> 
        <tr class="category-discount-row table-light"
            data-category="<?php echo  $category_view['category_name'];?>">
            <td colspan="6" class="text-right"><strong>Discount:
               </strong></td>
            <td style="text-align:right"><?php echo  number_format($category_view['subtotal']- $category_view['total'],0);?></td>
             <td></td>
        </tr>
		<tr class="category-total-row table-light"
            data-category="<?php echo  $category_view['category_name'];?>">
            <td colspan="6" class="text-right"><strong>Total:</strong></td>
            <td style="text-align:right"><?php echo  number_format($category_view['total']);?> </td>
             <td></td>
        </tr>
         <?php } ?>
        

    <?php 
        } 
    } 
    ?>
</tbody>
                 <tfoot>

                     <tr>
                         <td style="text-align:right" colspan="6" class="text-right text-primary">
                             <h5><strong>Final Floor Total:</strong></h5>
                         </td>
                         <td style="text-align:right"><?php echo number_format($floor_view['total']);?></td>
  <td></td>
                     </tr>
                 </tfoot>
             </table>
         </div>

         <?php  $i++; }  ?>
         <div class="">
            
             <table  class="table invoice-items-table items">
                 <tr>
                     <td width="50%" style="font-weight:bold; font-size:10px;">
                       
                           
                                 
                   <p> BANK DETAIL FOR PAYMENT: </p>
                    <p> HIGH CREATION INTERIOR PROJECTS PRIVATE LIMITED</p>
                     <p>Account No - CA/ 50200067427841 WITH HDFC BANK</p>
                     <p>IFSC - HDFC0000975</p>
                     
                              
                       
                     </td>
                     <td style="text-align:right">
                         
                             <?php 
                $grand_total = 0; 
                $floor_no = 1;
                foreach ($proposal_floors as $floor_view) {
                    $grand_total += $floor_view['total']; ?>
                             <h4 style="text-align:right; padding:0; margin:0"> <?= $floor_view['floor_name']; ?> Total : <?= number_format($floor_view['total'], 0); ?></h4>
                             <?php 
                    $floor_no++;
                } 
                ?>
                                             <h4>Site Handling Charges :  <?= number_format($proposals_info->siteHandlingAmount, 0); ?></h4>

                           <!--  <h3 style="text-align:right; color:green">Grand Total : <?= number_format($grand_total,0); ?></h3>-->
              <h3 style="font-size:14px; color: red; text-align:right; ">Grand Total : <?= number_format($proposals_info->totalToBePaid, 0); ?></h3>
                         
                     </td>
                 </tr>
             </table>
         </div>

      

         <table>
             <tr>
                 <td colspan="2">
                      <p style="font-weight:400">Notes : <?php echo $proposals_info->notes;?></p>

                 </td>
             </tr>


         </table>
        <?php if($proposals_info->exclusive_product) { ?>
            <table>
            <tr>
                <td colspan="8"><b><?php echo $proposals_info->exclusive_product ;?></b> </td>
            </tr>
               <?php
$j = 1;
$total_sum = 0;

foreach ($exclusive_product as $exclusive) {
    $total_sum += $exclusive['total']; // add each total
?>
    <tr>
        <td><?php echo $j; ?></td>
        <td><?php echo $exclusive['item_name']; ?></td>
        <td><?php echo $exclusive['description']; ?></td>
        <td><?php echo $exclusive['area']; ?></td>
        <td><?php echo $exclusive['unit']; ?></td>
        <td style="text-align:right"><?php echo $exclusive['price']; ?></td>
        <td style="text-align:right"><?php echo $exclusive['total']; ?></td>
        <td><?php echo $exclusive['brand']; ?></td>
    </tr>
<?php
    $j++;
}
?>
<tr>
    <td colspan="3"></td>
    <td colspan="3" style="text-align:right"><strong>Total Amount</strong></td>
    <td style="text-align:right"><strong><?php echo number_format($total_sum, 2); ?></strong></td>
    <td></td>
</tr>
 

           

         </table>
        <?php } ?>
         <!-- Terms & Conditions Table (No CSS styles) -->
         <table border="1" cellpadding="6" cellspacing="0">
             <thead>
                 <tr>
                     <th>#</th>
                     <th>Details</th>
                 </tr>
             </thead>
             <tbody>
                 <tr>
                     <td>1)</td>
                     <td>GST 18% included as per Govt norms.</td>
                 </tr>
                 <tr>
                     <td>2)</td>
                     <td>Water and Electricity supply will be provided by client.</td>
                 </tr>
                 <tr>
                     <td>3)</td>
                     <td>The entire material viz.; chemicals, tools etc. shall be kept in your safe custody at site,
                         which shall be used on the daily basis by labours. The responsibility of the entire material
                         will be customers if customer resident at the site.</td>
                 </tr>
                 <tr>
                     <td>4)</td>
                     <td>This Project Value is an Estimated Cost based on Information provided by the Client regarding
                         their work requirement. Actual Cost may change once all Designs, Elements &amp; Measurement of
                         Project are finalized.</td>
                 </tr>
                 <tr>
                     <td>5)</td>
                     <td>If there is any change or Addition, then, Unit Price in this Quote Shall be Revised According
                         to Design , Material &amp; Labour Charge.</td>
                 </tr>
                 <tr>
                     <td>6)</td>
                     <td>Final Billing shall be done on actual quantities and measurements. The Same Shall be checked
                         and Certified by both the Parties.</td>
                 </tr>
                 <tr>
                     <td>7)</td>
                     <td>Estimated Cost of Electrical work included, but, it will be charged and billed as per actual
                         work.</td>
                 </tr>
                 <tr>
                     <td>8)</td>
                     <td>The Client or Owner of the Property or Authorised Person as mentioned in Agreement shall take
                         the responsibility in case of a delay in completion deadline due to lack of timely payments as
                         mentioned in the payment schedule.</td>
                 </tr>
                 <tr>
                     <td>9)</td>
                     <td>*All material specification as per manufacturing company norms which is selected by
                         Client/designer/Engg etc.</td>
                 </tr>
                 <tr>
                     <td>10)</td>
                     <td>If the project cancellation request received within 48H from order booking then 100% refund of
                         the booking amount. And if cancellation request is received after 48 Hours then HC Interior
                         shall deduct 5% of project value as design or marketing cost. Balance amount shall be refund.
                         (Kindly visit for more details <a href="https://hcinterior.in/cancelletion-policy">https://hcinterior.in/cancelletion-policy</a> OR kindly visit our
                         website <a href="https://hcinterior.in">www.hcinterior.in</a> For cancellation policy)</td>
                 </tr>
                 <tr>
                     <td>11)</td>
                     <td>Sqft includes 5% wastage in wooden &amp; modular work.</td>
                 </tr>
                 <tr>
                     <td>12)</td>
                     <td>Site handling charge will be 7% of project value.</td>
                 </tr>
             </tbody>
         </table>

         <p>(Note:- Item or Work Specification, which is not mentioned In the Quote, will not be supplied by the
             Company. If, Customer is required to get it done, then, it will be Chargeable basis.</p>

         <!-- Payment Terms -->
         <table border="1" cellpadding="6" cellspacing="0">
             <thead>
                 <tr>
                     <th>Payment Terms: (Civil + Modular work)</th>
                     <th>Payment Terms: (Only Modular work)</th>
                 </tr>
             </thead>
             <tbody>
                 <tr>
                     <td>
                         <ul>
                             <li>10% advance along with order booking.</li>
                             <li>10% after designer visit (handover to design team)</li>
                             <li>40% before start of site work</li>
                             <li>20% before start of factory work</li>
                             <li>20% before dispatch</li>
                         </ul>
                     </td>
                     <td>
                         <ul>
                             <li>10% advance along with order booking.</li>
                             <li>10% after designer visit (handover to design team)</li>
                             <li>40% before start of factory work (at the time of validation)</li>
                             <li>40% before dispatch</li>
                         </ul>
                     </td>
                 </tr>
             </tbody>
         </table>

         <p>Note:- Payment terms may vary as per scope of work.</p>

         <p>Material Specifications:-</p>

         <h2 style="text-align:center"> HIGH CREATION INTERIOR PROJECTS PVT.LTD.</h2>
         <!-- Material Specifications Table (No CSS styles) -->
         <table border="1" cellpadding="6" cellspacing="0">
             <thead>
                 <tr>
                     <th>#</th>
                     <th>Details</th>
                 </tr>
             </thead>
             <tbody>
                 <tr>
                     <td>1</td>
                     <td>
                         <strong>WOOD:-</strong> HCIPPL HDMR(High Density Moisture Resistant) will be use
                         for whole carcase of all wooden modular & thickness of HDMRis 16.75 mm with prelam & 16.5mm
                         Century raw MDF will be use in Shutter panel.
                         <br><br>
                         <strong>Benifits of HCIPPL HDMR:-</strong><br>
                         1. No chipping<br>
                         2. Maximum screw holding strenth<br>
                         3. Stronger than Ply wood<br>
                         4. Density 700-850 kg/m3<br><br>
                         <strong>Benifits of Prelam:-</strong><br>
                         1. No Bubble of laminate<br>
                         2. Fade resistance<br>
                         3. Scretch resistance<br>
                         4. Easy to clean.<br><br>
                         ( Including cost of all materials, Transportation, cutting and placing to the required size,
                         labour charges, sundries, wastage etc. at all levels complete as per drawings and as per
                         direction of designer-in-Charge). with 10yr warranty*
                     </td>
                 </tr>
                 <tr>
                     <td>2</td>
                     <td><strong>INTERNAL FINISH:-</strong> Prelam fabric texture.</td>
                 </tr>
                 <tr>
                     <td>3</td>
                     <td>
                         <strong>EXTERNAL FINISH:-</strong> 1mm* thick Laminate (Laminate thickness as per selected
                         folders/brands) will be use on external finish of the wood & edge finished with 1.0 to 1.3 mm
                         thick PVC beeding with internal storage & fixing with L-patty(as per condition), 50mm & 19mm
                         gypsum screw including all Necessary - Fittings are requirment Make:- surface as per design
                         need & designer's selection.( Finish will change acc to client's choice-price may vary acc to
                         material) material costs - Veneer <strong>(Price upto Rs 2000/sheet) Laminates-Price upto Rs
                             1200. (If laminates were choose from merino, century the difference of sheet price will be
                             pay by the client.</strong> (Thickness of Laminate can be vary as per manufacturer Norms.)
                     </td>
                 </tr>
                 <tr>
                     <td>4</td>
                     <td>
                         <strong>HARDWARES:-</strong> Hardware & fittings as, <strong>Century or Equivalent</strong>
                         (with 10 years warranty).(Handle range upto Rs 350 rs/ pc for 12inches ,8inches-300/rs, 4
                         inches 150/rs, drawers channels-soft close channels. (if channels will increase the difference
                         will pay by the client's)
                     </td>
                 </tr>
                 <tr>
                     <td>5</td>
                     <td>
                         <strong>KITCHEN:-</strong> "<strong>Century or Equivalent</strong>" make will be provided for
                         Chanels, Baskets or hardware. Wire baskets will be provided and max 7 nos of baskets will be
                         provided including grain trolley/ multi-purpose basket/bottle pullout/cup-saucer/bin- basket &
                         GTPT etc. Price of Innotech baskets will be extra. Profile Handles will provided. Max 2 nos of
                         Aluminium Profile shutter provided. Chimney, Hob, geyser & CP fittings will not provided by
                         company.
                     </td>
                 </tr>
                 <tr>
                     <td>6</td>
                     <td>
                         <strong>ELECTRICITY:-</strong> "<strong>KEI/POLYCAB/RR CABEL</strong>" make will be provide for
                         Wiring. 0.75 sqmm to 4 sqmm wire will be provided. Make of Switch/Socket will be
                         "<strong>Anchor-Penta</strong>" (If other brand were choose, difference of price will be pay by
                         the client). If complete wiring not done then mcb will not be provided at the same price.
                     </td>
                 </tr>
             </tbody>
         </table>

         <p>I accept all term & condition. Please Read term & condition from
             <a href="https://hcinterior.in/term-and-condition">https://hcinterior.in/term-and-condition</a>,
             or kindly visit our website <a href="http://www.hcinterior.in">www.hcinterior.in</a> for term & condition.
         </p>

         <table>
             <tr>
                 <td style="height:400px">
                     <br><br>
                     <br><br>
                     <br><br>
                     Signature (Client or owner of the Property).------------

                 </td>
             </tr>
             <tr>
                 <td style="text-align:center">
                     HIGH CREATION INTERIOR PROJECTS PVT.LTD.

                 </td>
             </tr>
         </table>
     </div>



 </div>

 <style>
.section-row td {
    background-color: #95b3d7;
    color: #000;
    font-weight: bold;
}

.section-title td {

    color: #000;
    font-weight: bold;
    border: 1px solid #000;

}

h4.head {
    background: #ffddc8;
    padding: 10px;
    margin: 0;
    text-align: center;
}

.grand_total {
    text-align: right;

}


.grand_total h4 {
    font-size: 14px;
    color: #024892ff;
}

.grand_total h3 {
    font-size: 16px;
    font-weight: bold;
    color: #046c1cff;
}

.main_detail {
    display: flex;
    justify-content: space-between;
    padding: 0 10px;
}
 </style>