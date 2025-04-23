<?php

namespace src\Models;

class Feedback {
    private static $feedbacks = [];
    private static $nextId = 1;

    public function create($data) {
        $data['id'] = self::$nextId++;
        $data['created_at'] = date('Y-m-d H:i:s');
        self::$feedbacks[] = $data;
        return $data;
    }

    public function getAll() {
        return array_values(self::$feedbacks);
    }

    public function getByPlaceId($place_id) {
        return array_values(array_filter(self::$feedbacks, function($f) use ($place_id) {
            return $f['place_id'] == $place_id;
        }));
    }
}