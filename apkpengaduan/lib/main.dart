import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'providers/pengaduan_provider.dart';
import 'providers/kategori_provider.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/home_screen.dart';
import 'screens/pengaduan/listpengaduan.dart';
import 'screens/pengaduan/createpengaduan.dart';
import 'screens/pengaduan/detailpengaduan.dart';
import 'screens/pengaduan/editpengaduan.dart';
import 'screens/profile/profile.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => PengaduanProvider()),
        ChangeNotifierProvider(create: (_) => KategoriProvider()),
      ],
      child: MyApp(),
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
      ),
      home: LoginScreen(),
      routes: {
        '/login': (context) => LoginScreen(),
        '/register': (context) => RegisterScreen(),
        '/home': (context) => const HomeScreen(),
        '/pengaduan': (context) => const PengaduanListScreen(),
        '/create-pengaduan': (context) => const PengaduanFormScreen(),
        '/profile': (context) => ProfileScreen(user: {}),
      },
      onGenerateRoute: (settings) {
        if (settings.name!.startsWith('/detail-pengaduan/')) {
          final id = settings.name!.split('/').last;
          return MaterialPageRoute(
            builder: (context) => PengaduanDetailScreen(id: id),
          );
        }
        if (settings.name!.startsWith('/edit-pengaduan/')) {
          final id = settings.name!.split('/').last;
          return MaterialPageRoute(
            builder: (context) => PengaduanFormScreen(id: id),
          );
        }
        return null;
      },
    );
  }
}
