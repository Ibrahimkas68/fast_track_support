@extends('errors.layout')

@section('title', 'Trop de demandes')
@section('message', 'Veuillez patienter une minute avant de réessayer.')
@section('link', url('/tickets'))
@section('action', 'Revenir à la file')
