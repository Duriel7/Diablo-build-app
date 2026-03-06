import 'dart:convert';
import 'package:diablo_build_app/services/user_service.dart';
import 'package:http/http.dart' as http;
import '../models/build_model.dart';

class ApiService {
  //API URL for the backend API from enviornment variables
  static const String apiIp = String.fromEnvironment('API_IP', defaultValue: 'localhost');
  static const String apiPort = String.fromEnvironment('API_PORT', defaultValue: '8080');
  static const String baseUrl = "http://$apiIp:$apiPort/api";

  Future<List<Build>> fetchBuilds({int page = 1}) async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/builds?page=$page'));

      if (response.statusCode == 200) {
        final Map<String, dynamic> responseData = jsonDecode(response.body);
        
        final List<dynamic> buildsJson = responseData['data']['builds'];
        
        return buildsJson.map((item) => Build.fromJson(item)).toList();
      } else {
        throw Exception("Erreur serveur : ${response.statusCode}");
      }
    } catch (e) {
      print("Erreur de connexion : $e");
      return [];
    }
  }

  Future<bool> createBuild(Map<String, dynamic> buildData) async {
    final url = Uri.parse("http://$apiIp:$apiPort/api/build/create");
    final userService = UserService();
    final userData = await userService.getLocalUser();
    final token = userData?['token'];
    
    try {
      final response = await http.post(
        url,
        headers: {"Content-Type": "application/json", "Authorization": "Bearer $token",},
        body: jsonEncode(buildData),
      );
      return response.statusCode == 201;
    } catch (e) {
      return false;
    }
  }
}