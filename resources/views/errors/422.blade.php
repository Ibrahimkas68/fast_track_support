@extends('errors.layout')

@section('title', 'Paramètres invalides')
@section('message', 'Les paramètres de consultation ne sont pas valides. Réinitialisez les filtres.')
@section('link', url('/tickets'))
@section('action', 'Réinitialiser les filtres')
