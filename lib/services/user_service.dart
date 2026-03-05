import 'dart:convert';

import 'package:diablo_build_app/models/user_model.dart';
import 'package:geolocator/geolocator.dart';
import 'package:haptic_feedback/haptic_feedback.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class UserService {
  //Register URL for the backend API from enviornment variables
  static const String apiIp = String.fromEnvironment('API_IP', defaultValue: 'localhost');
  static const String apiPort = String.fromEnvironment('API_PORT', defaultValue: '8080');
  static const String registerUrl = "http://$apiIp:$apiPort/api/register";

  Future<Position?> getCurrentLocation() async {
    bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return null;

    LocationPermission permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return null;
    }
    
    return await Geolocator.getCurrentPosition();
  }

  void triggerSuccessVibration() async {
      if (await Haptics.canVibrate()) {
        await Haptics.vibrate(HapticsType.success);
      }
    }

  Future<Map<String, dynamic>?> registerUser(User user) async {
    final url = Uri.parse("http://$apiIp:$apiPort/api/register");
    
    try {
      final response = await http.post(
        url,
        headers: {"Content-Type": "application/json"},
        body: jsonEncode(user.toJson()),
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return null;
    } catch (e) {
      print("Erreur : $e");
      return null;
    }
  }

  //Login
  Future<void> loginUser(String email, String password) async {
    final url = Uri.parse("http://$apiIp:$apiPort/api/login");
    try {
      final response = await http.post(
        url,
        headers: {"Content-Type": "application/json"},
        body: jsonEncode({"email": email, "password": password}),
      );

      final Map<String, dynamic> responseData = jsonDecode(response.body);

      if (response.statusCode == 200 && responseData['success'] == true) {
        final data = responseData['data'];
        if (data is Map && data['user'] is Map) {
          final Map<String, dynamic> userMap = Map<String, dynamic>.from(data['user']);
          
          if (data['token'] != null) {
            userMap['token'] = data['token'].toString();
          }
          print("DONNÉES USER REÇUES DU SERVEUR : $userMap");
          await saveUserLocally(userMap);
          triggerSuccessVibration();
          return;
        }
      } 
      
      throw responseData['message'] ?? "Identifiants invalides.";

    } catch (e) {
      print("Erreur de login : $e");
      rethrow; 
    }
  }

  //Logout
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('user_session');
  }

  Future<void> saveUserLocally(Map<String, dynamic> userData) async {
    final prefs = await SharedPreferences.getInstance();
    String userJson = jsonEncode(userData);
    await prefs.setString('user_session', userJson);
  }

  Future<Map<String, dynamic>?> getLocalUser() async {
    final prefs = await SharedPreferences.getInstance();
    String? userJson = prefs.getString('user_session');
    if (userJson != null) {
      return jsonDecode(userJson);
    }
    return null;
  }
}