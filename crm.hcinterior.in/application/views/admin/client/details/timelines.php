<?php 


 $user_id = $this->session->userdata('user_id');
                           $user_type          = $this->session->userdata('user_type'); 
                           $designation_id     = $this->session->userdata('designations_id');
                           $assign_team = $this->db->where('user_id', $user_id)->get('tbl_client_assign_team')->row();  

                           


// $hand_over_date = $client_details->hand_over_request_date;
// // print_r($hand_over_date);
// $base_date = $hand_over_date;

$hand_over_date = !empty($client_details->hand_over_request_date) ? $client_details->hand_over_request_date : null;
//print_r($hand_over_date);
$base_date = $hand_over_date;

$amount = $freeze_boq_amount;
$designer_site_visit_date = $base_date ;
// FUNCTION
function add_days($date, $day, $day2 = null) {

    // First date
    $start = date('d-m-Y', strtotime("+$day days", strtotime($date)));

    // Agar day2 diya hai to range return karo
    if (!empty($day2)) {
        $end = date('d-m-Y', strtotime("+$day2 days", strtotime($date)));
        return $start . ' - ' . $end;
    }

    // warna single date
    return $start;
}


$amount = $freeze_boq_amount; // already hai tere paas

if ($amount >= 500000 && $amount <= 1000000) {
    $type = 'low';   // 5–10 lakh
} elseif ($amount > 1000000 && $amount <= 2000000) {
    $type = 'mid';   // 10–20 lakh
} else {
    $type = 'high';  // 20 lakh above
}

 
if ($type == 'low') {
  $site_start_date = add_days($base_date, 15);
  $factory_start_date = add_days($base_date, 15);

$washroom_plumbing = add_days($base_date, 7,10);

$design_presentation = add_days($base_date, 4);


$design_finalization = add_days($base_date, 11,12);
$wall_design = add_days($base_date, 12,16);
$material_section = add_days($base_date, 17,18);
$three_d_design = add_days($base_date, 15);
$site_validation = add_days($base_date, 30);
$mom_checklist = '';
$wall_electrical_drawing = add_days($base_date, 2);
$correction_drawing = add_days($base_date, 3);
$production_drawing_for_approval = add_days($base_date, 7);
$dispatch_date = '';
// $handover_date = add_days($base_date, $day);

} elseif ($type == 'mid') {
  $site_start_date = add_days($base_date, 20);
  $factory_start_date = add_days($base_date, 20);

$washroom_plumbing = add_days($base_date, 10,15);

$design_presentation = add_days($base_date, 4, 5);


$design_finalization = add_days($base_date, 11,13);
$wall_design = add_days($base_date, 13,19);
$material_section = add_days($base_date, 20,22);
$three_d_design = add_days($base_date, 15);
$site_validation = add_days($base_date, 45);
$mom_checklist = '';
$wall_electrical_drawing = add_days($base_date, 2);
$correction_drawing = add_days($base_date, 4);
$production_drawing_for_approval = add_days($base_date, 8);
$dispatch_date = '';
// $handover_date = add_days($base_date, $day);

} else {
   $site_start_date = add_days($base_date, 25);
   $factory_start_date = add_days($base_date, 25);

$washroom_plumbing = add_days($base_date, 15,20);

$design_presentation = add_days($base_date, 4, 5);


$design_finalization = add_days($base_date, 11,14);
$wall_design = add_days($base_date, 14,23);
$material_section = add_days($base_date, 23,25);
$three_d_design = add_days($base_date, 15);
$site_validation = add_days($base_date, 60);
$mom_checklist = '';
$wall_electrical_drawing = add_days($base_date, 2);
$correction_drawing = add_days($base_date, 5);
$production_drawing_for_approval = add_days($base_date, 9);
$dispatch_date = '';
// $handover_date = add_days($base_date, $day);

}



?>

 

<?php  if($user_type == 1 || check_permission('design_timeline_date_edit')) { ?>


<?php }  else { ?>

<style>
.edit-icon {
    display:none;
}

.editable-date {
    pointer-events: none;
}
    </style>
<?php } ?>

<?php if(!($user_type == 1 || check_permission('timeline_mark_done'))) { ?>
<style>
    .timeline-done-btn{
        display: none !important;
        /* ya agar hide nahi karna to:
        pointer-events: none;
        opacity: .5;
        cursor: not-allowed;
        */
    }
</style>
<?php } ?>


 <?php

 $client_id = $client_details->client_id;

$teamMembers = $this->db
    ->select('cat.member_profile, ad.fullname')
    ->from('tbl_client_assign_team cat')
    ->join('tbl_account_details ad', 'ad.user_id = cat.user_id', 'left')
    ->where('cat.client_id', $client_id)
    ->get()
    ->result();


  //  print_r($teamMembers);

$designers = [];
$projectManagers = [];

foreach ($teamMembers as $member) {

    if (strtolower(trim($member->member_profile)) == 'designer') {
        $designers[] = $member->fullname;
    }

    if (
        strtolower(trim($member->member_profile)) == 'project manager'  
    ) {
        $projectManagers[] = $member->fullname;
    }
}

$designer_names = !empty($designers)
    ? implode(', ', $designers)
    : 'NA';

$project_manager_names = !empty($projectManagers)
    ? implode(', ', $projectManagers)
    : 'NA';



$latestProposal = $this->db
    ->select('totalToBePaid')
    ->from('proposals')
    ->where('client_id', $client_id)
    ->order_by('id', 'DESC')
    ->limit(1)
    ->get()
    ->row();

$project_value = !empty($latestProposal) ? $latestProposal->totalToBePaid : '0';


 if(isset($_POST['ajax']) && $_POST['ajax'] == 1){

    $client_id = $_POST['client_id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    // allow only valid fields (security)
 $allowed = [
    'designer_site_visit_date',
    'site_start_date',
    'factory_start_date',
    'design_presentation',
    'design_finalization',
    'wall_design',
    'material_selection',
    'three_d_design',
    'site_validation',
    'washroom_plumbing',
    'correction_drawing',
    'production_drawing',
    'dispatch_date',
    'mom_checklist',
    'wall_electrical_drawing'
];

    if(!in_array($field, $allowed)){
        echo "invalid";
        exit;
    }

    $check = $this->db->get_where('timeline_manual_dates', ['client_id'=>$client_id])->row();

    if($check){
        $this->db->where('client_id', $client_id)
                 ->update('timeline_manual_dates', [$field => $value]);
    } else {
        $insert = ['client_id'=>$client_id, $field => $value];
        $this->db->insert('timeline_manual_dates', $insert);
    }

    echo "success";
    exit;
}
 

$existing = $this->db
    ->get_where('timeline_manual_dates', ['client_id' => $client_id])
    ->row();


    if(isset($_POST['action']) && $_POST['action']=='mark_done'){

    $client_id = $_POST['client_id'];
    $task_name = $_POST['task_name'];

    // timeline_manual_dates table se task_name ke according date fetch karein
    $manualRow = $this->db
        ->where('client_id', $client_id)
        ->get('timeline_manual_dates')
        ->row();

    $task_date = null;
    if ($manualRow && isset($manualRow->$task_name) && !empty($manualRow->$task_name)) {
        $task_date = trim($manualRow->$task_name);
    } elseif (!empty($_POST['task_date'])) {
        $task_date = trim($_POST['task_date']);
    }

    $completed_at = date('Y-m-d H:i:s');

    // Calculate delay_days (difference between task_date and completed_at)
    $delay_days = null;
    if (!empty($task_date)) {
        // Agar date range ho jaise "27-05-2026 - 29-05-2026", to last date leni hai
        if (preg_match_all('/\b\d{1,4}[-\/]\d{1,2}[-\/]\d{1,4}\b/', $task_date, $matches) && !empty($matches[0])) {
            $target_date_str = end($matches[0]);
        } else {
            $target_date_str = trim($task_date);
        }

        $ts_target = strtotime($target_date_str);
        $ts_completed = strtotime($completed_at);

        if ($ts_target !== false && $ts_target > 0 && (int)date('Y', $ts_target) > 2000 && $ts_completed !== false) {
            $date_target = new DateTime(date('Y-m-d', $ts_target));
            $date_completed = new DateTime(date('Y-m-d', $ts_completed));
            $diff = $date_target->diff($date_completed);
            $delay_days = (int)$diff->format('%r%a');
        }
    }

    $exists = $this->db
        ->where('client_id',$client_id)
        ->where('task_name',$task_name)
        ->get('timeline_task_status')
        ->row();

    $data = [
        'client_id'    => $client_id,
        'task_name'    => $task_name,
        'task_date'    => $task_date,
        'delay_days'   => $delay_days,
        'status'       => 'Done',
        'completed_by' => $this->session->userdata('user_id'),
        'completed_at' => $completed_at
    ];

    if($exists){
        $this->db
            ->where('id',$exists->id)
            ->update('timeline_task_status',$data);
    }else{
        $this->db->insert('timeline_task_status',$data);
    }

    echo 'success';
    exit;
}

$has_valid_base_date = false;
if (!empty($base_date) && strtotime($base_date) > 0 && date('Y-m-d', strtotime($base_date)) !== '1970-01-01') {
    $has_valid_base_date = true;
}

if (!$existing && $has_valid_base_date) {
    
    $insertData = [
        'client_id'                 => $client_id,
        'designer_site_visit_date' => add_days($base_date, 0),
        'site_start_date'          => $site_start_date,
        'factory_start_date'       => $factory_start_date ?? '',
        'design_presentation'      => $design_presentation,
        'design_finalization'      => $design_finalization,
        'wall_design'              => $wall_design,
        'material_selection'       => $material_section,
        'three_d_design'           => $three_d_design,
        'site_validation'          => $site_validation,
        'washroom_plumbing'        => $washroom_plumbing,
        'correction_drawing'       => $correction_drawing,
        'production_drawing'       => $production_drawing_for_approval,
        'dispatch_date'            => $dispatch_date,
        'mom_checklist'            => $mom_checklist,
        'wall_electrical_drawing'  => $wall_electrical_drawing,
    ];

    $insert = $this->db->insert('timeline_manual_dates', $insertData);
}


$manual = $this->db
    ->get_where('timeline_manual_dates', ['client_id'=>$client_id])
    ->row();


    $statusRows = $this->db
    ->where('client_id', $client_id)
    ->get('timeline_task_status')
    ->result();

$taskStatus = [];

foreach($statusRows as $row){
    $taskStatus[$row->task_name] = $row;
}
?>

<?php
function final_date($manual, $auto){
    return !empty($manual) ? $manual : $auto;
}
?>


<?php 
// Show condition match hone par true hoga
$can_view = ($user_type == 1 || check_permission('design_timeline_view')); 
?>



 
 <section class="panel panel-custom" >
     <div class="panel-heading">
         <div class="panel-title">
             <strong><?= lang('Timelines') ?></strong>
         </div>
     </div>

     <div class="panel-body" style="<?php echo $can_view ? '' : 'display: none !important;'; ?>">

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
                 
                 <table class="timeline-table">
                     <tr>
                         <th colspan="4" class="timeline-title">DESIGNING TIMELINE</th>
                     </tr>
                     <tr>
                         <td class="text-left"><b>Site Name :</b> <?php echo $client_details->address;?>,  <?php echo $client_details->city;?></td>
                         <td colspan="3"><b>Designer Name :</b> <?= $designer_names ?></td>
                     </tr>
                     <tr>
                         <td class="text-left"><b>Project Manager Name :</b>
                            <?= $project_manager_names ?></td>
                         <td colspan="3"><b>Project Value :</b><?= number_format($project_value, 0) ?> /-</td>
                     </tr>
                     <tr>
                         <td class="text-left"><b>Client Name :</b> Mr. <?php echo $client_details->name;?>
                         </td>
                         <td colspan="3"><b>Customer ID :</b><?php echo $client_details->client_sr_id;?></td>
                     </tr>
                    <tr class="highlight">
                <td colspan="2">DESIGNER SITE VISIT DATE</td>
                <td colspan="2">

                  <span class="editable-date"
          data-field="designer_site_visit_date"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->designer_site_visit_date ?? '', add_days($base_date, 0)) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

    <?php
$isDone = !empty($taskStatus['designer_site_visit_date']) &&
          $taskStatus['designer_site_visit_date']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="designer_site_visit_date">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>

    
                   
                        </td>
                    </tr>

            <tr class="light-highlight">
                <td colspan="2">SITE WORK START DATE</td>
                <td>
    <span class="editable-date"
          data-field="site_start_date"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->site_start_date ?? '', $site_start_date) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

    <?php
$isDone = !empty($taskStatus['site_start_date']) &&
          $taskStatus['site_start_date']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="site_start_date">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
</td>
            </tr>

            <!-- FACTORY WORK START DATE (NEW FIELD) -->
<tr class="light-highlight">
    <td colspan="2">FACTORY WORK START DATE</td>
    <td>
        <span class="editable-date" data-field="factory_start_date" data-client="<?= $client_id ?>">
            <?= final_date($manual->factory_start_date ?? '', $factory_start_date) ?>
        </span>
        <i class="fa fa-edit edit-icon"></i>

        <?php $isDone = !empty($taskStatus['factory_start_date']) && $taskStatus['factory_start_date']->status == 'Done'; ?>
        <button class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn" data-client="<?= $client_id ?>" data-task="factory_start_date">
            <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
        </button>
    </td>
</tr>


                 </table>

             </div>

             <!-- Civil Work -->
             <div class="tab-pane fade" id="civil_timeline">
                  
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
    <span class="editable-date"
          data-field="site_start_date"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->site_start_date ?? '', $site_start_date) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

    <?php
$isDone = !empty($taskStatus['site_start_date']) &&
          $taskStatus['site_start_date']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="site_start_date">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
</td>
                     </tr>
                     <tr>
                         <td>1</td>
                         <td class="text-left">Layout Finalization</td>
                        <td rowspan="6">
   

     <span class="editable-date"
          data-field="washroom_plumbing"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->washroom_plumbing ?? '', $washroom_plumbing) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

     <?php
$isDone = !empty($taskStatus['washroom_plumbing']) &&
          $taskStatus['washroom_plumbing']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="washroom_plumbing">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
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
                        <td>
                             <span class="editable-date"
          data-field="design_presentation"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->design_presentation ?? '', $design_presentation) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>
    
     <?php
$isDone = !empty($taskStatus['design_presentation']) &&
          $taskStatus['design_presentation']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="design_presentation">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
</td>         

                     </tr>
                     <tr>
                         <td>2</td>
                         <td class="text-left">Design Finalization</td>
                        <td> 
                    <span class="editable-date"
          data-field="design_finalization"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->design_finalization ?? '', $design_finalization) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

      <?php
$isDone = !empty($taskStatus['design_finalization']) &&
          $taskStatus['design_finalization']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="design_finalization">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
</td>


                     </tr>
                     <tr>
                         <td>3</td>
                         <td class="text-left">Wall Designs Modular 2D Working Drawings</td>
                       <td> 
                    <span class="editable-date"
          data-field="wall_design"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->wall_design ?? '', $wall_design) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

     <?php
$isDone = !empty($taskStatus['wall_design']) &&
          $taskStatus['wall_design']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="wall_design">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>

</td>

                     </tr>
                     <tr>
                         <td>4</td>
                         <td class="text-left">Material Selection (Includes Handles & Paint)</td>
                         <td> 
                        
                         <span class="editable-date"
          data-field="material_selection"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->material_selection ?? '', $material_section) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

    
     <?php
$isDone = !empty($taskStatus['material_selection']) &&
          $taskStatus['material_selection']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="material_selection">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>

</td>

                     </tr>
                     <tr>
                         <td>5</td>
                         <td class="text-left">3D Design File to be submitted (Optional)</td>
                         <td>
                            
                         <span class="editable-date"
          data-field="three_d_design"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->three_d_design ?? '', $three_d_design) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

     <?php
$isDone = !empty($taskStatus['three_d_design']) &&
          $taskStatus['three_d_design']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="three_d_design">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
    
    </td>

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
                        <td>
                             <span class="editable-date"
          data-field="site_validation"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->site_validation ?? '', $site_validation) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>


       <?php
$isDone = !empty($taskStatus['site_validation']) &&
          $taskStatus['site_validation']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="site_validation">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
    
    
     </td>


                     </tr>
                     <tr>
                         <td>2</td>
                         <td class="text-left">MOM Checklist</td>
                         <td colspan="1">
                          <span class="editable-date"
          data-field="mom_checklist"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->mom_checklist ?? '', $mom_checklist) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

     <?php
$isDone = !empty($taskStatus['mom_checklist']) &&
          $taskStatus['mom_checklist']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="mom_checklist">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>

</td>
                     </tr>
                     <tr>
                         <td>3</td>
                         <td class="text-left">Wall Electrical Drawings</td>
                         <td colspan="1">After <span class="editable-date"
          data-field="wall_electrical_drawing"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->wall_electrical_drawing ?? '', $wall_electrical_drawing) ?>
    </span>
   
    
    of validation  <i class="fa fa-edit edit-icon"></i>

   <?php
$isDone = !empty($taskStatus['wall_electrical_drawing']) &&
          $taskStatus['wall_electrical_drawing']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="wall_electrical_drawing">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>


</td>
                     </tr>
                 </table>

             </div>

             <!-- Production & Dispatch -->
             <div class="tab-pane fade" id="production_dispatch_timeline">
                  
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
                         
                         <td>After  <span class="editable-date"
          data-field="correction_drawing"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->correction_drawing ?? '', $correction_drawing) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>

      <?php
$isDone = !empty($taskStatus['correction_drawing']) &&
          $taskStatus['correction_drawing']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="correction_drawing">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>


    </td>

                     </tr>
                     <tr>
                         <td>2</td>
                         <td class="text-left">Production Drawings for Approval</td>
                        <td>
                            <span class="editable-date"
          data-field="production_drawing"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->production_drawing ?? '', $production_drawing_for_approval) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>


    
      <?php
$isDone = !empty($taskStatus['production_drawing']) &&
          $taskStatus['production_drawing']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="production_drawing">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
    
    </td>

                     </tr>
                     <tr>
                         <td>3</td>
                         <td class="text-left">Dispatch Date</td>
                         <td colspan="1">Date :  <span class="editable-date"
          data-field="dispatch_date"
          data-client="<?= $client_id ?>">
        <?= final_date($manual->dispatch_date ?? '', $dispatch_date) ?>
    </span>
    <i class="fa fa-edit edit-icon"></i>


      <?php
$isDone = !empty($taskStatus['dispatch_date']) &&
          $taskStatus['dispatch_date']->status == 'Done';
?>

<button
    class="btn btn-xs <?= $isDone ? 'btn-success' : 'btn-warning' ?> timeline-done-btn"
    data-client="<?= $client_id ?>"
    data-task="dispatch_date">
    <?= $isDone ? 'Done ✓' : 'Mark Done' ?>
</button>
    
    </td>
                     </tr>

                    <tr class="light-highlight">
                            <td colspan="3">
                            Handover Date :
                            <b>40-45 days</b> after Production
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

<script>
document.addEventListener("DOMContentLoaded", function(){

    // click on edit icon OR text
    document.querySelectorAll('.editable-date, .edit-icon').forEach(el => {

        el.addEventListener('click', function(){

            let span = this.classList.contains('editable-date')
                ? this
                : this.previousElementSibling;

            if(span.querySelector('input')) return;

            let current = span.innerText.trim();

            let input = document.createElement('input');
            input.type = "text";
            input.value = current;
            input.className = "form-control";
            input.style.width = "200px";

            span.innerHTML = '';
            span.appendChild(input);
            input.focus();

            // SAVE ON BLUR
            input.addEventListener('blur', function(){
                saveDate(span, input.value);
            });

            // SAVE ON ENTER
            input.addEventListener('keypress', function(e){
                if(e.key === 'Enter'){
                    input.blur();
                }
            });

        });

    });

    function saveDate(span, value){

        let field = span.getAttribute('data-field');
        let client_id = span.getAttribute('data-client');

        fetch("", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `ajax=1&field=${field}&value=${value}&client_id=${client_id}`
        })
        .then(res => res.text())
        .then(res => {
            span.innerHTML = value || 'NA';
        })
        .catch(err => {
            alert('Error saving');
        });

    }

});


$(document).on('click','.timeline-done-btn',function(){

    let $btn = $(this);
    let client_id = $btn.data('client');
    let task_name = $btn.data('task');

    // UI se bhi date nikaal lein (fallback ke liye)
    let container = $btn.closest('td');
    if(!container.length || !container.find('.editable-date').length){
        container = $btn.closest('tr');
    }
    let dateSpan = container.find('.editable-date[data-field="' + task_name + '"]');
    if(!dateSpan.length){
        dateSpan = container.find('.editable-date');
    }
    let task_date = dateSpan.length ? dateSpan.text().trim() : '';

    $btn.prop('disabled', true).text('Please wait...');

    $.post('',{
        action:'mark_done',
        client_id:client_id,
        task_name:task_name,
        task_date:task_date
    },function(res){
        location.reload();
    }).fail(function(){
        $btn.prop('disabled', false).text('Mark Done');
    });

});
</script>



<style>
    .editable-date {
    cursor: pointer;
    padding: 3px 6px;
}

.editable-date:hover {
    background: #fff3cd;
}

.edit-icon {
    margin-left: 5px;
    cursor: pointer;
    color: #007bff;
}
</style>

