import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'providers/pengaduan_provider.dart';
import 'providers/kategori_provider.dart';
import 'screens/auth/auth_wrapper.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/home_screen.dart';
import 'screens/pengaduan/list_pengaduan.dart';
import 'screens/pengaduan/create_pengaduan.dart';
import 'screens/pengaduan/detail_pengaduan.dart';
import 'screens/profile/profile.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => PengaduanProvider()),
        ChangeNotifierProvider(create: (_) => KategoriProvider()),
      ],
      child: const MyApp(),
    ),
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Aplikasi Pengaduan',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
        fontFamily: 'Roboto',
        // Menambahkan theme untuk consistent design
        appBarTheme: AppBarTheme(
          backgroundColor: Colors.blue.shade600,
          foregroundColor: Colors.white,
          elevation: 0,
          centerTitle: true,
        ),
        cardTheme: CardTheme(
          elevation: 3,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.blue.shade600,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            elevation: 2,
          ),
        ),
      ),
      home: const AuthWrapper(),
      routes: {
        '/login': (context) => const LoginScreen(),
        '/register': (context) => const RegisterScreen(),
        '/home': (context) => const HomeScreen(),
        '/pengaduan': (context) => const ListPengaduanScreen(),
        '/create-pengaduan': (context) => const CreatePengaduanScreen(),
        '/profile': (context) => ProfileScreen(user: const {}),
      },
      onGenerateRoute: (settings) {
        // Route untuk detail pengaduan dengan parameter ID
        if (settings.name!.startsWith('/detail-pengaduan/')) {
          final id = settings.name!.split('/').last;
          return MaterialPageRoute(
            builder: (context) => DetailPengaduanScreen(id: id),
          );
        }

        // Route untuk edit pengaduan (jika diperlukan di masa depan)
        if (settings.name!.startsWith('/edit-pengaduan/')) {
          final id = settings.name!.split('/').last;
          return MaterialPageRoute(
            builder: (context) => CreatePengaduanScreen(editId: id),
          );
        }

        return null;
      },
    );
  }
}
