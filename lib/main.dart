import 'package:flutter/material.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Flutter Demo',
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
      home: const MyHomePage(title: 'Diablo Build App'),
    );
  }
}
