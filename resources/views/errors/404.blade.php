@extends('errors.layout')

@section('title', 'Page introuvable')
@section('message', 'Cette page ou ce ticket n’existe pas.')
@section('link', url('/tickets'))
@section('action', 'Revenir à la file')
