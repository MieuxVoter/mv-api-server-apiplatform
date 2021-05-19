#!/bin/env python
# coding: utf-8
import yaml
import os

# Util to help sort and fix the lists of words.

words_path = r'words.en.yml'
confs_path = r'confs.en.yml'

words = {}  # keys: 'age', 'being', 'color', 'material', 'origin', 'qualifier', 'quality', 'quantity', 'shape', 'size'
confs = {}  # config for this util, see load_confs()

def load_words():
    global words
    with open(words_path) as file:
        words = yaml.load(file, Loader=yaml.FullLoader)
    print(words.keys())

def load_confs():
    global confs
    with open(confs_path) as file:
        confs = yaml.load(file, Loader=yaml.FullLoader)
    if not confs:
        confs = {
            'allowed_duplicates': [],
        }

def save_words():
    global words
    with open(words_path, 'w') as file:
        yaml.dump(words, file)

def save_confs():
    global confs
    with open(confs_path, 'w') as file:
        yaml.dump(confs, file)

load_confs()
load_words()
unique_words = []
unique_words_adjectives = []
for adjective_type in words:
    # I. Sort alphabetically
    words[adjective_type].sort()

    # II. Fix case
    for i, word in enumerate(words[adjective_type]):
        words[adjective_type][i] = word.title()

    # III. Remove Duplicates
    for word in words[adjective_type]:
        if word in unique_words:
            i = unique_words.index(word)
            other_adjective_type = unique_words_adjectives[i]

            if word in confs['allowed_duplicates']:
                continue

            print("Duplicate!  %s  is in both  %s (1) and %s (2)" % (
                word, adjective_type, other_adjective_type
            ))

            if adjective_type == other_adjective_type:
                words[adjective_type].remove(word)
                print("Deleting the duplicate `%s' automatically…" % (word))
                continue

            print("Type `1` or `2` to keep that one, `i` to ignore, anything else to skip.")
            decision = input("What should we do? [12i] : ")
            
            if decision in ["1"]:
                print("Deleting the word from %s…" % (other_adjective_type))
                words[other_adjective_type].remove(word)
                save_words()
                
            elif decision in ["2"]:
                print("Deleting the word from %s…" % (adjective_type))
                words[adjective_type].remove(word)
                save_words()

            elif decision in ["I", "i"]:
                print("Ignoring the duplicate word `%s'…" % (word))
                confs['allowed_duplicates'].append(word)
                save_confs()
            
            else:  #if decision in ["s", "S", "", anything else really]:
                print("Skipping…")

        else:
            unique_words.append(word)
            unique_words_adjectives.append(adjective_type)

    # …

    # X. Finally, save to disk for good measure
    save_words()

print("%d words found." % (len(unique_words)))
print("Done.")
        
