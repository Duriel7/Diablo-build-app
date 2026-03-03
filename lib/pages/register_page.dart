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

        // L'APPEL RÉEL EST ICI MAINTENANT :
        bool success = await _userService.registerUser(newUser);

        if (success) {
          _userService.triggerSuccessVibration();
          _showSuccessDialog(pos);
        } else {
          throw Exception("Le serveur a refusé l'inscription.");
        }

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
            onPressed: () => Navigator.pop(context),
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
      body: SingleChildScrollView( // Ajout du scroll pour éviter que le clavier cache tout
        padding: const EdgeInsets.all(20.0),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              // NOUVEAU : CHAMP PSEUDO
              TextFormField(
                decoration: const InputDecoration(labelText: "Pseudo", border: OutlineInputBorder()),
                validator: (v) => v!.isEmpty ? "Nom de héros requis" : null,
                onSaved: (v) => _nickname = v!,
              ),
              const SizedBox(height: 15),

              // NOUVEAU : CHAMP VILLE
              TextFormField(
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