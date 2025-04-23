<?php

namespace src\Controllers;

use src\Models\Place;
use src\helpers\Response;

class PlacesController
{
    public function createPlace($data)
    {
        $place = new Place();
        $result = $place->create($data);

        if ($result) {
            Response::sendSuccess($result);
        } else {
            Response::sendError("Failed to create place.");
        }
    }

    public function getPlaces()
    {
        $place = new Place();
        $places = $place->getAll();
        Response::sendSuccess($places);
    }

    public function updatePlace($id, $data)
    {
        $place = new Place();
        $result = $place->update($id, $data);

        if ($result) {
            Response::sendSuccess($result);
        } else {
            Response::sendError("Failed to update place.");
        }
    }

    public function deletePlace($id)
    {
        $place = new Place();
        $result = $place->delete($id);

        if ($result) {
            Response::sendSuccess("Place deleted successfully.");
        } else {
            Response::sendError("Failed to delete place.");
        }
    }

    public function getPlace($id)
    {
        $place = new Place();
        $result = $place->find($id);

        if ($result) {
            Response::sendSuccess($result);
        } else {
            Response::sendError("Place not found.");
        }
    }
}