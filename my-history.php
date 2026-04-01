$sql = "SELECT * FROM trips 
        WHERE end_date < '$today' 
        AND (user_id = '$user_id' OR id IN (SELECT trip_id FROM trip_members WHERE user_id = '$user_id'))";