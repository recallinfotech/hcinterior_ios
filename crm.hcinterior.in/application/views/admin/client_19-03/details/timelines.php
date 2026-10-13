 <section class="panel panel-custom">
     <div class="panel-heading">
         <div class="panel-title">
             <strong><?= lang('Timelines') ?></strong>
         </div>
     </div>

     <div class="panel-body">

         <!-- Tabs -->
         <ul class="nav nav-tabs">
             <li class="active">
                 <a href="#designing_timeline" data-toggle="tab">
                     <i class="fa fa-pencil"></i> Designing Timeline
                 </a>
             </li>
             <li>
                 <a href="#civil_timeline" data-toggle="tab">
                     <i class="fa fa-building"></i> Civil Work Timeline
                 </a>
             </li>
             <li>
                 <a href="#modular_timeline" data-toggle="tab">
                     <i class="fa fa-cubes"></i> Modular Work Timeline
                 </a>
             </li>
             <li>
                 <a href="#site_validation_timeline" data-toggle="tab">
                     <i class="fa fa-check-square-o"></i> Site Validation Timeline
                 </a>
             </li>
             <li>
                 <a href="#production_dispatch_timeline" data-toggle="tab">
                     <i class="fa fa-truck"></i> Production & Dispatch Timeline
                 </a>
             </li>
         </ul>

         <!-- Tab Content -->
         <div class="tab-content" style="margin-top:15px;">

             <!-- Designing -->
             <div class="tab-pane fade in active" id="designing_timeline">
                 <?php 
                  $designing_timeline = $this->db->where(array('client_id' => $client_details->client_id))->get('timelines')->row();
          //  print_r($designing_timeline);     ?>
                 <table class="timeline-table">
                     <tr>
                         <th colspan="4" class="timeline-title">DESIGNING TIMELINE</th>
                     </tr>
                     <tr>
                         <td class="text-left"><b>Site Name :</b> <?php echo $designing_timeline->site_name;?></td>
                         <td colspan="3"><b>Designer Name :</b> <?php echo $designing_timeline->desginer_name;?></td>
                     </tr>
                     <tr>
                         <td class="text-left"><b>Project Manager Name :</b>
                             <?php echo $designing_timeline->project_manager_name;?></td>
                         <td colspan="3"><b>Project Value :</b><?php echo $designing_timeline->project_value;?></td>
                     </tr>
                     <tr>
                         <td class="text-left"><b>Client Name :</b> Mr. <?php echo $designing_timeline->client_name;?>
                         </td>
                         <td colspan="3"><b>Customer ID :</b> HCINT10<?php echo $designing_timeline->customer_id;?></td>
                     </tr>
                    <tr class="highlight">
    <td colspan="2">DESIGNER SITE VISIT DATE</td>
    <td colspan="2">

     <input type="date" value="<?= display_date_or_na($designing_timeline->desginer_site_visit_date) ?>" class="form-control">  
       
        <?php // echo substr($designing_timeline->desginer_site_visit_date, 0, 10); ?>
     </td>
</tr>

<tr class="light-highlight">
    <td colspan="2">SITE WORK START DATE</td>
    <td colspan="2">
       <input type="date" value="<?= display_date_or_na($designing_timeline->site_work_start_date) ?>" class="form-control">  
    </td>
</tr>
                 </table>

             </div>

             <!-- Civil Work -->
             <div class="tab-pane fade" id="civil_timeline">
                 <?php 
                  $timelines_civil_work = $this->db->where(array('timelines_id' => $designing_timeline->id))->get('timelines_civil_work')->row();
          //  print_r($timelines_civil_work);
            ?>
                 <table class="timeline-table">
                     <tr>
                         <th colspan="5" class="section-title">CIVIL WORK TIMELINE</th>
                     </tr>
                     <tr class="sub-header">
                         <th>S. No.</th>
                         <th class="text-left">Details</th>
                         <th>Time Line</th>

                     </tr>
                     <tr>
                         <td></td>
                         <td class="text-left"><b>SITE START DATE</b></td>
                         <td>
    <?= add_days_or_na(
        $designing_timeline->site_work_start_date,
        $timelines_civil_work->site_start_date
    ) ?>
</td>
                     </tr>
                     <tr>
                         <td>1</td>
                         <td class="text-left">Layout Finalization</td>
                        <td rowspan="6">
    <?= date_range_or_na(
        $designing_timeline->site_work_start_date,
        $timelines_civil_work->layout_finalization
    ) ?>
</td>
                     </tr>
                     <tr>
                         <td>2</td>
                         <td class="text-left">Dismantling Drawings</td>
                     </tr>
                     <tr>
                         <td>3</td>
                         <td class="text-left">Brick Work Drawing</td>
                     </tr>
                     <tr>
                         <td>4</td>
                         <td class="text-left">Washroom Plumbing & Electrical Drawings</td>


                     </tr>
                     <tr>
                         <td>5</td>
                         <td class="text-left">False Ceiling Drawings (Lights & Looping)</td>
                     </tr>
                     <tr>
                         <td>6</td>
                         <td class="text-left">Washroom and Full House Tiles Finalization</td>

                     </tr>
                     <tr class="note-row">
                         <td colspan="3">Note : All Civil Drawings to be finalised except Wall Electrical Drawings</td>
                     </tr>
                 </table>

             </div>

             <!-- Modular Work -->
             <div class="tab-pane fade" id="modular_timeline">
                 <?php 
                  $timelines_modular_work = $this->db->where(array('timelines_id' => $designing_timeline->id))->get('timelines_modular_work')->row();
          //  print_r($timelines_modular_work);
            ?>
                 <table class="timeline-table">
                     <tr>
                         <th colspan="5" class="section-title">MODULAR WORK TIMELINE</th>
                     </tr>
                     <tr class="sub-header">
                         <th>S. No.</th>
                         <th class="text-left">Details</th>
                         <th colspan="3">Submission Date</th>
                     </tr>
                     <tr>
                         <td>1</td>
                         <td class="text-left">Design Presentation (PPT)</td>
                        <td><?= add_days_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_modular_work->design_presentation
) ?></td>

                     </tr>
                     <tr>
                         <td>2</td>
                         <td class="text-left">Design Finalization</td>
                        <td><?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_modular_work->design_finalization
) ?></td>


                     </tr>
                     <tr>
                         <td>3</td>
                         <td class="text-left">Wall Designs Modular 2D Working Drawings</td>
                       <td><?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_modular_work->wall_designs_modular_2d_working_drawings
) ?></td>

                     </tr>
                     <tr>
                         <td>4</td>
                         <td class="text-left">Material Selection (Includes Handles & Paint)</td>
                         <td><?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_modular_work->material_selection_handle_and_paint
) ?></td>

                     </tr>
                     <tr>
                         <td>5</td>
                         <td class="text-left">3D Design File to be submitted (Optional)</td>
                         <td><?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_modular_work->three_d_design_file_to_be_submitted
) ?></td>

                     </tr>
                     <tr>
                         <td>6</td>
                         <td class="text-left">Wall Electrical Drawings</td>
                         <td colspan="1">Before Validation</td>
                     </tr>
                     <tr class="note-row">
                         <td colspan="3">Note : All items in the BOQ to be finalised</td>
                     </tr>
                 </table>

             </div>

             <!-- Site Validation -->
             <div class="tab-pane fade" id="site_validation_timeline">
                 <?php 
                  $timelines_site_validation = $this->db->where(array('timelines_id' => $designing_timeline->id))->get('timelines_site_validation')->row();
           // print_r($timelines_site_validation);
            ?>
                 <table class="timeline-table">
                     <tr>
                         <th colspan="5" class="section-title">SITE VALIDATION TIMELINE</th>
                     </tr>
                     <tr class="sub-header">
                         <th>S. No.</th>
                         <th class="text-left">Details</th>
                         <th>Submission Date</th>

                     </tr>
                     <tr>
                         <td>1</td>
                         <td class="text-left">Site Validation</td>
                        <td><?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_site_validation->site_validation
) ?></td>


                     </tr>
                     <tr>
                         <td>2</td>
                         <td class="text-left">MOM Checklist</td>
                         <td colspan="1"><?php echo $timelines_site_validation->mom_checklist;?></td>
                     </tr>
                     <tr>
                         <td>3</td>
                         <td class="text-left">Wall Electrical Drawings</td>
                         <td colspan="1">After <?php echo $timelines_site_validation->wall_electrical_drawings;?> days of validation</td>
                     </tr>
                 </table>

             </div>

             <!-- Production & Dispatch -->
             <div class="tab-pane fade" id="production_dispatch_timeline">
                 <?php 
                  $timelines_production_and_dispatch = $this->db->where(array('timelines_id' => $designing_timeline->id))->get('timelines_production_and_dispatch')->row();
          //  print_r($timelines_production_and_dispatch);
            ?>
                 <table class="timeline-table">
                     <tr>
                         <th colspan="3" class="section-title">PRODUCTION AND DISPATCH TIMELINES</th>
                     </tr>
                     <tr class="sub-header">
                         <th>S. No.</th>
                         <th class="text-left">Details</th>
                         <th>Submission Date</th>

                     </tr>
                     <tr>
                         <td>1</td>
                         <td class="text-left">Correction Drawings</td>
                         
                         <td>After <?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_production_and_dispatch->correction_drawings
) ?></td>

                     </tr>
                     <tr>
                         <td>2</td>
                         <td class="text-left">Production Drawings for Approval</td>
                        <td><?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_production_and_dispatch->production_drawings_for_approval
) ?></td>

                     </tr>
                     <tr>
                         <td>3</td>
                         <td class="text-left">Dispatch Date</td>
                         <td colspan="1">Date :  <?php echo $timelines_production_and_dispatch->dispatch_date;?></td>
                     </tr>
                    <tr class="light-highlight">
<td colspan="3">
Handover Date :
<b><?= date_range_or_na(
    $designing_timeline->site_work_start_date,
    $timelines_production_and_dispatch->hand_over_date
) ?></b> after Production
</td>
</tr>
                 </table>

             </div>

         </div>

     </div>
 </section>



 <style>
.timeline-table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 13px;
}

.timeline-table th,
.timeline-table td {
    border: 1px solid #000;
    padding: 6px 8px;
    text-align: center;
}

.timeline-title {
    background: #a9d18e;
    font-weight: bold;
    font-size: 14px;
}

.section-title {
    background: #bdd7ee;
    font-weight: bold;
}

.sub-header {
    background: #6fa8dc;
    color: #000;
    font-weight: bold;
}

.note-row {
    background: #f4f4cc;
    text-align: left;
    font-style: italic;
}

.highlight {
    background: #c27ba0;
    color: #000;
    font-weight: bold;
}

.light-highlight {
    background: #f4cccc;
    font-weight: bold;
}

.text-left {
    text-align: left;
}
 </style>




 


<?php
function display_date_or_na($date, $format = null)
{
    $format = $format ?: config_item('date_format');

    if (empty($date)) return 'NA';

    $ts = strtotime($date);

    if ($ts === false || $ts <= 0) return 'NA';

    if (date('Y-m-d', $ts) === '01-01-1970') return 'NA';

    return strftime($format, $ts);
}

function add_days_or_na($baseDate, $days, $format = null)
{
    $format = $format ?: config_item('date_format');

    if (empty($baseDate) || empty($days)) return 'NA';

    $baseTs = strtotime($baseDate);
    if ($baseTs === false || $baseTs <= 0) return 'NA';

    $newTs = strtotime("+$days days", $baseTs);
    if ($newTs === false || date('Y-m-d', $newTs) === '01-01-1970') return 'NA';

    return strftime($format, $newTs);
}

function date_range_or_na($baseDate, $range)
{
    if (empty($baseDate) || empty($range)) return 'NA';

    $daysArray = explode(',', $range);
    $dates = [];

    foreach ($daysArray as $d) {
        $date = add_days_or_na($baseDate, trim($d));
        if ($date !== 'NA') $dates[] = $date;
    }

    return empty($dates) ? 'NA' : implode(' - ', $dates);
}
?>
