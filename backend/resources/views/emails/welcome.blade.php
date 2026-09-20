<x-mail::message>
# ¡Bienvenido, {{ $usuario->nombre }}!

Este es tu usuario: {{ $usuario->email }}

Gracias por registrarte en Butaka. Ya puedes explorar la cartelera y comprar entradas, y por cada compra acumularás puntos canjeables por descuentos.

<x-mail::button :url="config('butaka.frontend_url')">
Ver la cartelera
</x-mail::button>

Un saludo,<br>
{{ config('app.name') }}
</x-mail::message>
