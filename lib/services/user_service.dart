import 'dart:convert';

import 'package:diablo_build_app/models/user_model.dart';
import 'package:geolocator/geolocator.dart';
import 'package:haptic_feedback/haptic_feedback.dart';
import 'package:http/http.dart' as http;

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

  Future<bool> registerUser(User user) async {
    try {
      final response = await http.post(
        Uri.parse(registerUrl),
        headers: {"Content-Type": "application/json"},
        body: jsonEncode(user.toJson()),
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        return true;
      } else {
        print("Erreur serveur: ${response.body}");
        return false;
      }
    } catch (e) {
      print("Erreur réseau: $e");
      return false;
    }
  }
}