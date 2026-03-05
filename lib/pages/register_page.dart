import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import '../models/user_model.dart';
import '../services/user_service.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  final _userService = UserService();
  
  String _nickname = "";
  String _city = "";
  String _email = "";
  String _password = "";
  bool _isLoading = false;

  void _submitRegistration() async {
    if (_formKey.currentState!.validate()) {
      _formKey.currentState!.save();
      
      setState(() => _isLoading = true);

      try {
        Position? pos = await _userService.getCurrentLocation();
        
        final newUser = User(
          nickname: _nickname,
          city: _city,
          email: _email,
          password: _password,
          latitude: pos?.latitude,
          longitude: pos?.longitude,
        );

      final Map<String, dynamic>? responseData = await _userService.registerUser(newUser);

      if (responseData != null && responseData['success'] == true) {
        final data = responseData['data'];
        
        if (data is Map) {
          final dynamic rawUser = data['user'];
          
          if (rawUser != null && rawUser is Map) {
            final Map<String, dynamic> userMap = Map<String, dynamic>.from(rawUser);
            final dynamic rawToken = data['token'];
            if (rawToken != null) {
              userMap['token'] = rawToken.toString();
            }
            
            await _userService.saveUserLocally(userMap);
            _userService.triggerSuccessVibration();
            _showSuccessDialog(pos);
            return;
          }
        }
      }

      throw Exception(responseData?['message'] ?? "Erreur de structure de données serveur.");

      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text("Erreur : $e"), backgroundColor: Colors.red),
        );
      } finally {
        setState(() => _isLoading = false);
      }
    }
  }
  
  void _showSuccessDialog(Position? pos) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: const Color(0xFF1A1A1A),
        shape: RoundedRectangleBorder(side: BorderSide(color: Color(0xFFC5A059))),
        title: const Text("Âme enregistrée", style: TextStyle(color: Color(0xFFC5A059))),
        content: Text(
          "Bienvenue !\nPosition : ${pos?.latitude ?? 'Inconnue'}, ${pos?.longitude ?? 'Inconnue'}\nUn mail de validation arrive.",
          style: const TextStyle(color: Colors.white),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pushNamedAndRemoveUntil(context, '/home', (route) => false),
            child: const Text("REJOINDRE LE COMBAT", style: TextStyle(color: Color(0xFF8B0000), fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("REJOINDRE LE SANCTUAIRE")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                textCapitalization: TextCapitalization.words,
                decoration: const InputDecoration(labelText: "Pseudo", border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? "Nom de héros requis" : null,
                onSaved: (v) => _nickname = v!,
              ),
              const SizedBox(height: 15),

              TextFormField(
                textCapitalization: TextCapitalization.words,
                decoration: const InputDecoration(labelText: "Ville", border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? "La cité est requise" : null,
                onSaved: (v) => _city = v!,
              ),
              const SizedBox(height: 15),

              TextFormField(
                decoration: const InputDecoration(labelText: "Email", border: OutlineInputBorder()),
                keyboardType: TextInputType.emailAddress,
                validator: (v) => v!.contains('@') ? null : "Email invalide",
                onSaved: (v) => _email = v!,
              ),
              const SizedBox(height: 15),

              TextFormField(
                decoration: const InputDecoration(labelText: "Mot de passe", border: OutlineInputBorder()),
                obscureText: true,
                validator: (v) => v!.length < 6 ? "Trop court (6 mini)" : null,
                onSaved: (v) => _password = v!,
              ),
              const SizedBox(height: 30),

              _isLoading 
                ? const CircularProgressIndicator(color: Color(0xFFC5A059))
                : ElevatedButton(
                    onPressed: _submitRegistration,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFC5A059),
                      minimumSize: const Size(double.infinity, 50),
                    ),
                    child: const Text("S'INSCRIRE", style: TextStyle(color: Colors.black, fontWeight: FontWeight.bold)),
                  ),
            ],
          ),
        ),
      ),
    );
  }
}