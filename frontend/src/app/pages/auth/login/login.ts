import { Component, signal } from '@angular/core';
import { FormControl, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { CarritoService } from '../../../core/services/carrito.service';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    RouterLink,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatCardModule,
    MatIconModule
  ],
  templateUrl: './login.html',
  styleUrl: './login.scss',
})
export class Login {
  loginForm: FormGroup;
  error = signal('');
  cargando = signal(false);
  mostrarPassword = false;

  constructor(
    private authService: AuthService,
    private carritoService: CarritoService,
    private router: Router
  ) {
    this.loginForm = new FormGroup({
      email: new FormControl('', [Validators.required, Validators.email]),
      password: new FormControl('', [Validators.required]),
    });
  }

  onSubmit() {
    if (this.loginForm.invalid) return;

    this.cargando.set(true);
    this.error.set('');

    const { email, password } = this.loginForm.value;

    this.authService.login(email, password).subscribe({
      next: () => {
        // El carrito se recupera aquí, para que el contador del navbar aparezca
        // nada más entrar y no haya que visitar la pantalla del carrito
        this.carritoService.cargarCarrito();
        this.router.navigate(['/']);
      },
      error: (err) => {
        this.cargando.set(false);
        this.error.set(err.error?.message ?? 'Error al iniciar sesión');
      }
    });
  }
}
