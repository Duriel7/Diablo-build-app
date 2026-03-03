import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/build_model.dart';

class ApiService {
  //API URL for the backend API from enviornment variables
  static const String apiIp = String.fromEnvironment('API_IP', defaultValue: 'localhost');
  static const String apiPort = String.fromEnvironment('API_PORT', defaultValue: '8080');
  static const String baseUrl = "http://$apiIp:$apiPort/api";

  Future<List<Build>> fetchBuilds() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/builds'));

      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        
        final List<dynamic> buildsJson = responseData['data']; 
        
        return buildsJson.map((item) => Build.fromJson(item)).toList();
      } else {
        throw Exception("Erreur serveur : ${response.statusCode}");
      }
    } catch (e) {
      throw Exception("Erreur de connexion : $e");
    }
  }
}