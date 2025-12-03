/**
 * add_client.c
 *
 * This file contains the implementation for adding a new client to the system.
 * It includes functions for validating client data, storing it in the database,
 * and handling any errors that may arise during the process.
 *  Created on: 2024-06-15
 * By : Maxime ENTZ <@MaxENTZ>
*/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <fcntl.h>
#include <string.h>
#define SIZE_CLIENT_SECRET 10
#define BASIC_PATH "/var/www/iapel/"

static void to_upper(char *str)
{
    if (str == NULL)
        return;
    for (int i = 0; str[i] != '\0'; i++) {
        if (str[i] >= 'a' && str[i] <= 'z') {
            str[i] -= 32;
        }
    }
}


void create_client_secret(char *client_name, char * client_secret)
{
    for (int i = 0; client_name[i] != '\0'; i++) {
        if (i <= 3)
            break;
        client_secret[i] = client_name[i] - 32;
    }
}

int basic_validation(char *argv[], int argc)
{
    if (geteuid() != 0) {
        printf("Ce programme doit être exécuté en tant que root.\n");
        return 1;
    }
    if (argc != 2) {
        fprintf(stderr, "Usage: %s <client_name>\n", argv[0]);
        return 1;
    }
    if (client_name == NULL || argv[1][0] == '\0') {
        fprintf(stderr, "Le nom du client ne peut pas être vide.\n");
        return 1;
    }
    return 0;
}

int add_client_to_environment(char *client_name, char *client_secret)
{
    int fd = open("/var/www/iapel/.env", O_WRONLY | O_APPEND);
    char buffer[strlen(client_name)];

    for (int i = 0; client_name[i] != '\0'; i++)
        buffer[i] = client_name[i] - 32;
    if (fd == -1) {
        perror("Erreur lors de l'ouverture du fichier .env");
        return 1;
    }
    dprintf(fd, "%s_SECRET=%s\n", buffer, client_secret);
    close(fd);
    return 0;
}

int add_client_to_config(char *client_name, char *client_secret)
{
    int fd = open("/var/www/iapel/config/secrets.php", O_RDWR);
    char buffer_upper[strlen(client_name) + 1];
    char line[256];
    off_t file_size;
    char *file_content;
    
    if (fd == -1) {
        perror("Erreur lors de l'ouverture du fichier secrets.php");
        return 1;
    }
    strcpy(buffer_upper, client_name);
    to_upper(buffer_upper);
    file_size = lseek(fd, 0, SEEK_END);
    lseek(fd, 0, SEEK_SET);
    file_content = malloc(file_size + 1);
    if (file_content == NULL) {
        perror("Erreur d'allocation mémoire");
        close(fd);
        return 1;
    }
    read(fd, file_content, file_size);
    file_content[file_size] = '\0';
    char *last_bracket = strstr(file_content, "];");
    if (last_bracket == NULL) {
        fprintf(stderr, "Format du fichier secrets.php invalide\n");
        free(file_content);
        close(fd);
        return 1;
    }
    off_t insert_position = last_bracket - file_content;
    lseek(fd, insert_position, SEEK_SET);
    snprintf(line, sizeof(line), "    '%s' => env('%s_SECRET'),\n    ];", client_name, buffer_upper);
    if (write(fd, line, strlen(line)) == -1) {
        perror("Erreur lors de l'écriture dans secrets.php");
        free(file_content);
        close(fd);
        return 1;
    }
    ftruncate(fd, lseek(fd, 0, SEEK_CUR));
    free(file_content);
    close(fd);
    return 0;
}

int main(int argc, char *argv[])
{
    char *client_name;
    char *client_secret;

    if (basic_validation(argv, argc))
        return 1;
    client_name = argv[1];
    client_secret = malloc(SIZE_CLIENT_SECRET * sizeof(char) + 1);
    if (client_secret == NULL) {
        fprintf(stderr, "Erreur d'allocation mémoire.\n");
        return 1;
    }
    printf("Adding client: %s\n", client_name);
    create_client_secret(client_name, client_secret);
    if (add_client_to_environment(client_name, client_secret)) {
        free(client_secret);
        return 1;
    }
    if (add_client_to_config(client_name, client_secret)) {
        free(client_secret);
        return 1;
    }
    printf("Client '%s' ajouté avec succès.\n", client_name);
    free(client_secret);
    return 0;
}

