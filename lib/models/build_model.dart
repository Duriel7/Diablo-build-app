class Build {
  final int id;
  final String name;
  final String characterClass;
  final String? description;
  final int authorId;
  final String game;
  final bool isDraft;
  final int version;
  final String? imageRepository;
  final String? imageFileName;

  Build({
    required this.id,
    required this.name,
    required this.characterClass,
    this.description,
    required this.authorId,
    required this.game,
    required this.isDraft,
    required this.version,
    this.imageRepository,
    this.imageFileName,
  });

  // Building Dart object from my API JSON
  factory Build.fromJson(Map<String, dynamic> json) {
    return Build(
      id: json['Id'],
      name: json['Name'],
      characterClass: json['CharacterClass'],
      description: json['Description'],
      authorId: json['Author'],
      game: json['Game'],
      isDraft: json['IsDraft'],
      version: json['Version'],
      imageRepository: json['imageRepository'],
      imageFileName: json['imageFileName'],
    );
  }
}