import 'package:diablo_build_app/pages/login_page.dart';
import 'package:diablo_build_app/pages/register_page.dart';
import 'package:flutter/material.dart';
import 'package:diablo_build_app/pages/home_page.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  @override
  Widget build(BuildContext context) {
    
    return MaterialApp(
      title: 'Diablo Build App',
      theme: ThemeData(
        brightness: Brightness.dark,
        scaffoldBackgroundColor: const Color(0xFF1A1A1A), // equivalent of the --dark-bg in my CSS
        colorScheme: const ColorScheme.dark(
          primary: Color(0xFFC5A059), // equivalent of the --gold-accent in my CSS
          secondary: Color(0xFF660000), // equivalent of the --diablo-red in my CSS
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.black,
          titleTextStyle: TextStyle(color: Color(0xFFC5A059), fontSize: 20, fontWeight: FontWeight.bold),
        ),
      ),
      home: const MyHomePage(title: 'Sanctuaire : Forge'),
      routes: {
        '/home': (context) => const MyHomePage(title: 'Sanctuaire : Forge'),
        '/register': (context) => const RegisterPage(),
        '/login': (context) => const LoginPage(),
      },
    );
  }
}
