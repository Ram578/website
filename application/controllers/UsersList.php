<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Userslist extends CI_Controller {

	/**
	 * This is Userslist page controller.
	 */
	 
	public function index()
	{
		if(isset($this->session->userdata['EmployeeID']))
		{
			$this->load->model('adminmodel');
			
			$application_type = $_GET['type'];
			
			if($application_type == "pitch") 
			{
				$arrData['app_title'] = "Pitch Discrimination";
			}
			else if($application_type == "time") 
			{
				$arrData['app_title'] = "Time Discrimination";
			}
			else if($application_type == "tonal") 
			{
				$arrData['app_title'] = "Tonal Memory";
			}
						
			$arrData['application_type'] = $application_type;
			
			$arrData['Users'] = $this->adminmodel->FetchUsers($application_type);

			foreach ($arrData['Users'] as $key => &$value) 
			{
				$intScore = $this->adminmodel->FetchUserResult($value['id'], $application_type);

				$value['score'] = $intScore;

				$value['certile'] = $this->adminmodel->FetchCertileWRT($intScore, $value['age'], $value['gender'], $application_type);
			}
			
			$this->load->view('userslist', $arrData);
		}
		else
		{
			redirect('/admin', 'refresh');
		}
	}
	
	public function export($type)
	{
		$this->load->model('adminmodel');
		
		$arrData['Users'] = $this->adminmodel->FetchUsers($type);
		
		foreach ($arrData['Users'] as $key => &$value) 
		{
			$intScore = $this->adminmodel->FetchUserResult($value['id'], $type);
			
			//Check the application type for completed date and status
			if($type == "pitch") 
			{
				$value['status'] =  $value['pitch_status'];
				unset($value['time_completed_date']);
				unset($value['tonal_completed_date']);
			}
			else if($type == "time") 
			{
				$value['status'] =  $value['time_status'];
				unset($value['pitch_completed_date']);
				unset($value['tonal_completed_date']);
			}
			else if($type == "tonal")
			{
				//$value['status'] =  $value['tonal_status'];
				unset($value['pitch_completed_date']);
				unset($value['time_completed_date']);
			}
			
			unset($value['pitch_status']);
			unset($value['time_status']);
			unset($value['tonal_status']);
			if($type != "tonal") {
				// Check the Status & then assign the value
				if($value['status'] == 1) 
				{
					$value['status'] = "Next";
				}
				else if($value['status'] == 2)
				{
					$value['status'] = "More Examples";
				} 
				else
				{
					$value['status'] = "";
				}
			}

			$value['score'] = $intScore;

			$value['certile'] = $this->adminmodel->FetchCertileWRT($intScore, $value['age'], $value['gender'], $type);
		}
		
		// Enable to download this file
		$filename = ucfirst($type)."UsersList.csv";
		 
		header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: text/csv");
         
        $display = fopen("php://output", 'w');
        
		if($type != "tonal") {
			$arrHeaders = array('ID', 'First Name', 'Last Name', 'Age', 'Gender', 'File Number', 'Created Date', 'Completed Date', 'Active', 'Status', 'Score', 'Certile');
		} else {
			$arrHeaders = array('ID', 'First Name', 'Last Name', 'Age', 'Gender', 'File Number', 'Created Date', 'Completed Date', 'Active', 'Score', 'Certile');
		}
        
        
        fputcsv($display, array_values($arrHeaders), ",", '"');
       
		$users = $arrData['Users'];
		
		if(isset($users))    
		{
            foreach ($users as  $users)
			{
               fputcsv($display, array_values($users), ",", '"');
			}
		}
       
		fclose($display);
	}
	
}
