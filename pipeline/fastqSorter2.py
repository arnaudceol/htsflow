#!/usr/bin/python

import sys, os.path, os;

print sys.argv

if len(sys.argv) != 3:
   sys.exit("Need two arguments");

#########################
#### open input files #####
##########################

inf=["",""]
inf[0] = open(sys.argv[1])
inf[1] = open(sys.argv[2])

###########################
#### open output files ########
##########################

of1name = os.path.dirname(sys.argv[1])+"/sorted_"+os.path.basename(sys.argv[1])
of2name = os.path.dirname(sys.argv[2])+"/sorted_"+os.path.basename(sys.argv[2])

of1 = open(of1name,"w")
of2 = open(of2name,"w")

##########################################################
#### variable initialization and function declaration #########
########################################################

#### create data structures ( dictionaries ) to store additional
#### reads information ( the three lines after the read name )

ls1 = [{},{}]
ls2 = [{},{}]
ls3 = [{},{}]

#### counter for matching reads

bN = 0;

#### function to write matched reads to file 1 and 2

def do_write(name):
   global bN, of1, of2, ls1, ls2, ls3
   bN+=1;
   of1.write(name+"\n");
   of1.write(ls1[0][name]);
   of1.write(ls2[0][name]);
   of1.write(ls3[0][name]);
   of2.write(name+"\n");
   of2.write(ls1[1][name]);
   of2.write(ls2[1][name]);
   of2.write(ls3[1][name]);
   for i in [0,1]:
      del ls1[i][name];
      del ls2[i][name];
      del ls3[i][name];

##############
#### body #########
################

l = ["",""];
l[0] =inf[0].readline().split("/")[0].strip(); # remove eventual additional info following '/'
l[1] =inf[1].readline().split("/")[0].strip(); # remove eventual additional info following '/'
while l[0]!="" or l[1]!="":
   for i in [0,1]:
      if l[i] != "":
         # store read information ( sequence [ in ls1 ] ,
         # '+' [ in ls2 ] , and quality [ in ls3 ] )
         ls1[i][l[i]] = inf[i].readline();
         ls2[i][l[i]] = inf[i].readline();
         ls3[i][l[i]] = inf[i].readline();
      # if there is alreasy a read in the other dictionary
      # with the same name write it down
      if l[i] in ls1[1-i]:
         do_write(l[i]);
      l[i] = inf[i].readline().split("/")[0].strip();

#### write the unmatched reads to the end of file one

sN = [0,0]
for i in [0,1]:
   for name in ls1[i].iterkeys():
      sN[i]+=1;
      of1.write(name+"\n");
      of1.write(ls1[i][name]);
      of1.write(ls2[i][name]);
      of1.write(ls3[i][name]);

of1.close()
of2.close()

#### rename sorted files to the original ones

os.rename( of1name , sys.argv[1] )
os.rename( of2name , sys.argv[2] )

#### write out statistics

print "Found ",bN,"matching.";
print "  ",sN[0],"unmatched from 1 file."
print "  ",sN[1],"unmatched from 0 file."
