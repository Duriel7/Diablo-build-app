import 'package:diablo_build_app/pages/register_page.dart';
import 'package:diablo_build_app/services/user_service.dart';
import 'package:flutter/material.dart';
import 'package:diablo_build_app/pages/home_page.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final userService = UserService();
  final userData = await userService.getLocalUser();
  final bool loggedIn = userData != null;

  runApp(MyApp(isLoggedIn: loggedIn));
}

class MyApp extends StatelessWidget {
  final bool isLoggedIn;
  const MyApp({super.key, required this.isLoggedIn});
  @override
  Widget build(BuildContext context) {
    
    return MaterialApp(
      title: 'Diablo Build App',
      theme: ThemeData(
        brightness: Brightness.dark,
        scaffoldBackgroundColor: const Color(0xFF1A1A1A), // equivalent of the --dark-bg in my CSS
        colorScheme: const ColorScheme.dark(
          primary: Color(0xFFC5A059), // equivalent of the --gold-accent in my CSS
          secondary: Color(0xFF8B0000), // equivalent of the --diablo-red in my CSS
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.black,
          titleTextStyle: TextStyle(color: Color(0xFFC5A059), fontSize: 20, fontWeight: FontWeight.bold),
        ),
      ),
      //home: const MyHomePage(title: 'Sanctuaire : Forge'),
      home: isLoggedIn ? const MyHomePage(title: 'Sanctuaire : Forge') : const RegisterPage(),
      routes: {
        '/register': (context) => const RegisterPage(),
        '/home': (context) => const MyHomePage(title: 'Sanctuaire : Forge'),
      },
    );
  }
}
