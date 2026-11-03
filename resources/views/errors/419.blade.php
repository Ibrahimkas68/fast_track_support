@extends('errors.layout')

@section('title', 'Formulaire expiré')
@section('message', 'Votre session a expiré. Ouvrez un nouveau formulaire pour réessayer.')
@section('link', url('/tickets/create'))
@section('action', 'Ouvrir un nouveau formulaire')
